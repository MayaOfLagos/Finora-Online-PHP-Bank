<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Determine Laravel Application Path
|--------------------------------------------------------------------------
|
| For shared hosting, Laravel app files are stored outside public_html.
| This auto-detects the correct path for both local dev and production.
|
*/
$laravelPath = __DIR__.'/..';

// Check if deployed to shared hosting (finora_app folder exists)
if (is_dir('/home/txepiedg/finora_app/bootstrap')) {
    $laravelPath = '/home/txepiedg/finora_app';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
