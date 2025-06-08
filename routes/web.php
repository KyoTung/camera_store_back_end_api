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


// Route tạm tạo symlink storage:link
Route::get('/dev/storage-link', function () {
    // Chỉ cho phép chạy nếu là môi trường local/dev hoặc đã xác thực admin
    // Có thể thêm kiểm tra quyền tại đây nếu cần
    Artisan::call('storage:link');
    return 'Đã chạy xong php artisan storage:link!';
});
