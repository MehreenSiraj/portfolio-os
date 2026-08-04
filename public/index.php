<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Dual-path bootstrap (local vs Hostinger)
|--------------------------------------------------------------------------
|
| Local layout:   app root/public/index.php → ../vendor, ../bootstrap
| Hostinger:      public_html/index.php     → ../laravel_app/vendor, ../laravel_app/bootstrap
|
| public_path() is always this directory so asset URLs and storage:link target
| the web root (public_html on production).
|
*/

$laravelRoot = is_dir(__DIR__.'/../laravel_app')
    ? __DIR__.'/../laravel_app'
    : __DIR__.'/..';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelRoot.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelRoot.'/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
