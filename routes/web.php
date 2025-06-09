<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/db-test', function() {
    return DB::connection()->getDatabaseName();
});
use Illuminate\Support\Facades\Artisan;


