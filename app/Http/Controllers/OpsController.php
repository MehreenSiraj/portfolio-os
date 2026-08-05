<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class OpsController extends Controller
{
    /**
     * Allowed ops actions only. Plain-text Artisan output.
     *
     * @var list<string>
     */
    private const ACTIONS = [
        'migrate',
        'storage-link',
        'cache-clear',
        'optimize',
        'livewire-assets',
    ];

    public function __invoke(Request $request, string $action): Response
    {
        $configured = (string) config('ops.token', '');

        if ($configured === '') {
            throw new NotFoundHttpException;
        }

        $provided = (string) $request->query('token', '');

        if ($provided === '' || ! hash_equals($configured, $provided)) {
            throw new AccessDeniedHttpException('Invalid ops token.');
        }

        if (! in_array($action, self::ACTIONS, true)) {
            throw new NotFoundHttpException;
        }

        $output = match ($action) {
            'migrate' => $this->runMigrate(),
            'storage-link' => $this->runStorageLink(),
            'cache-clear' => $this->runCacheClear(),
            'optimize' => $this->runOptimize(),
            'livewire-assets' => $this->runLivewireAssets(),
        };

        return response($output, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function runMigrate(): string
    {
        $exit = Artisan::call('migrate', ['--force' => true]);

        return trim(Artisan::output())."\n[exit={$exit}]\n";
    }

    private function runStorageLink(): string
    {
        $target = storage_path('app/public');
        $link = public_path('storage');
        $lines = [];

        File::ensureDirectoryExists($target);

        if (is_link($link) || (file_exists($link) && ! is_dir($link))) {
            if (is_link($link) && realpath($link) === realpath($target)) {
                return "public/storage already linked to storage/app/public.\n";
            }
        }

        if (is_link($link)) {
            @unlink($link);
            $lines[] = 'Removed stale public/storage symlink.';
        }

        try {
            $exit = Artisan::call('storage:link');
            $lines[] = trim(Artisan::output());
            $lines[] = "[storage:link exit={$exit}]";

            if (is_link($link) || file_exists($link)) {
                $lines[] = 'Storage link OK.';

                return implode("\n", $lines)."\n";
            }
        } catch (Throwable $e) {
            $lines[] = 'storage:link failed: '.$e->getMessage();
        }

        // Shared hosting often blocks symlink(); copy tree instead.
        if (file_exists($link) && ! is_dir($link)) {
            @unlink($link);
        }

        File::ensureDirectoryExists($link);
        File::copyDirectory($target, $link);
        $lines[] = 'Symlink unavailable or incomplete; copied storage/app/public → public/storage.';
        $lines[] = 'Re-run this action after new public media uploads, or upload files under both paths.';

        return implode("\n", $lines)."\n";
    }

    private function runCacheClear(): string
    {
        $parts = [];

        foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
            $exit = Artisan::call($cmd);
            $parts[] = trim(Artisan::output())." [{$cmd} exit={$exit}]";
        }

        $parts[] = $this->runLoginAssetFix();

        return implode("\n", $parts)."\n";
    }

    /**
     * Hostinger-safe login repair: rewrite layouts so Livewire boots from jsDelivr,
     * and restore vendor/livewire/.../dist JS from GitHub when missing.
     */
    private function runLoginAssetFix(): string
    {
        $lines = ['login-asset-fix:'];

        $guestPath = resource_path('views/layouts/guest.blade.php');
        $guestHtml = <<<'BLADE'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'PinSA') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen text-[15px] text-ink">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-12">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-24 left-1/2 h-72 w-[36rem] -translate-x-1/2 rounded-full bg-accent/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <p class="font-mono text-[11px] font-medium tracking-[0.2em] text-muted uppercase">PinSA</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-ink">Portfolio OS</h1>
            </div>

            <div class="rounded-2xl border border-line bg-surface/90 p-7 shadow-[0_20px_50px_-30px_rgba(20,23,31,0.35)] backdrop-blur">
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScriptConfig
    <script type="module">
        import { Livewire, Alpine } from 'https://cdn.jsdelivr.net/gh/livewire/livewire@v4.3.5/dist/livewire.esm.js';
        window.Alpine = Alpine;
        Livewire.start();
    </script>
</body>
</html>

BLADE;

        // Only rebuild the guest layout when its Livewire boot is actually broken —
        // otherwise this repair would overwrite the designed layout on every run.
        $guestNeedsRepair = ! is_file($guestPath)
            || ! str_contains((string) file_get_contents($guestPath), 'cdn.jsdelivr.net/gh/livewire');

        if (! $guestNeedsRepair) {
            $lines[] = 'guest.blade.php already boots Livewire from the CDN — left untouched';
        } else {
            try {
                File::put($guestPath, $guestHtml);
                $lines[] = 'wrote guest.blade.php path='.$guestPath.' bytes='.strlen($guestHtml);
                $lines[] = 'guest_md5='.md5($guestHtml);
            } catch (Throwable $e) {
                $lines[] = 'guest write failed: '.$e->getMessage();
            }
        }

        $appPath = resource_path('views/layouts/app.blade.php');
        if (is_file($appPath)) {
            $app = (string) file_get_contents($appPath);
            if (! str_contains($app, 'cdn.jsdelivr.net/gh/livewire')) {
                $snippet = <<<'SNIP'
@livewireScriptConfig
    <script type="module">
        import { Livewire, Alpine } from 'https://cdn.jsdelivr.net/gh/livewire/livewire@v4.3.5/dist/livewire.esm.js';
        window.Alpine = Alpine;
        Livewire.start();
    </script>
SNIP;
                if (str_contains($app, '@livewireScripts')) {
                    $app = str_replace('@livewireScripts', $snippet, $app);
                } elseif (str_contains($app, '@livewireScriptConfig')) {
                    // already script-config only; append CDN boot after it
                    $app = str_replace('@livewireScriptConfig', $snippet, $app);
                } else {
                    $app = rtrim($app)."\n".$snippet."\n";
                }
                File::put($appPath, $app);
                $lines[] = 'patched app.blade.php for CDN Livewire';
            } else {
                $lines[] = 'app.blade.php already has CDN Livewire';
            }
        } else {
            $lines[] = 'app.blade.php missing at '.$appPath;
        }

        Artisan::call('view:clear');
        $lines[] = 'view:clear after layout write';

        $lines[] = trim($this->runLivewireAssets());

        return implode("\n", $lines);
    }

    private function runOptimize(): string
    {
        $exit = Artisan::call('optimize');

        return trim(Artisan::output())."\n[exit={$exit}]\n";
    }

    /**
     * Ensure Livewire dist JS exists under vendor (shared-host FTP often omits it).
     * Downloads from the installed package tag on GitHub when missing.
     */
    private function runLivewireAssets(): string
    {
        $distDir = base_path('vendor/livewire/livewire/dist');
        $lines = [];
        File::ensureDirectoryExists($distDir);

        $files = [
            'livewire.min.js',
            'livewire.min.js.map',
            'livewire.js',
            'manifest.json',
        ];

        $version = $this->installedLivewireVersion();
        $tag = $version !== '' ? $version : 'v4.3.5';
        $lines[] = "Livewire package version: {$tag}";
        $lines[] = "dist dir: {$distDir}";

        foreach ($files as $file) {
            $target = $distDir.DIRECTORY_SEPARATOR.$file;
            if (is_file($target) && filesize($target) > 0) {
                $lines[] = "OK existing {$file} (".filesize($target).' bytes)';

                continue;
            }

            $url = "https://raw.githubusercontent.com/livewire/livewire/{$tag}/dist/{$file}";
            $lines[] = "Fetching {$url}";

            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 60,
                        'header' => "User-Agent: PinSA-ops-livewire-assets\r\n",
                    ],
                    'ssl' => [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                    ],
                ]);
                $body = @file_get_contents($url, false, $ctx);
                if ($body === false || $body === '') {
                    $lines[] = "FAIL download empty/false for {$file}";

                    continue;
                }
                $written = File::put($target, $body);
                $lines[] = "WROTE {$file} ({$written} bytes)";
            } catch (Throwable $e) {
                $lines[] = "FAIL {$file}: ".$e->getMessage();
            }
        }

        $min = $distDir.DIRECTORY_SEPARATOR.'livewire.min.js';
        $lines[] = 'livewire.min.js exists='.(is_file($min) ? 'yes' : 'no')
            .' size='.(is_file($min) ? (string) filesize($min) : '0');

        return implode("\n", $lines)."\n";
    }

    private function installedLivewireVersion(): string
    {
        $installed = base_path('vendor/composer/installed.json');
        if (! is_file($installed)) {
            return '';
        }

        try {
            $json = json_decode((string) file_get_contents($installed), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return '';
        }

        $packages = $json['packages'] ?? $json;
        if (! is_array($packages)) {
            return '';
        }

        foreach ($packages as $package) {
            if (! is_array($package)) {
                continue;
            }
            if (($package['name'] ?? '') === 'livewire/livewire') {
                return (string) ($package['pretty_version'] ?? $package['version'] ?? '');
            }
        }

        return '';
    }
}
