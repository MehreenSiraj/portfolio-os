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

        return implode("\n", $parts)."\n";
    }

    private function runOptimize(): string
    {
        $exit = Artisan::call('optimize');

        return trim(Artisan::output())."\n[exit={$exit}]\n";
    }
}
