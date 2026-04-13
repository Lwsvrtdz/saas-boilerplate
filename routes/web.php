<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
        'mode' => 'api-first',
        'frontend_url' => config('boilerplate.frontend_url'),
    ]);
});
