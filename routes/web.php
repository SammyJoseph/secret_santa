<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn() => Auth::check()
    ? redirect()->route('user.profile')
    : view('user.register'));

Route::post('/registro', [UserController::class, 'store'])->name('user.register');
Route::post('/temp-upload', [UserController::class, 'tempUpload'])->name('user.temp-upload');
Route::get('/temp-image/{filename}', [UserController::class, 'getTempImage'])->name('user.temp-image');

Route::get('/perfil', function () {
    return view('user.profile');
})->name('user.profile');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('admin/users', AdminUserController::class);
});