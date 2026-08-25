<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

do {
    $keepRunning = frankenphp_handle_request(function () use ($kernel) {
        $request = Request::capture();

        $response = $kernel->handle($request)->send();

        $kernel->terminate($request, $response);
    });
} while ($keepRunning);
