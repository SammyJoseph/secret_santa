<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('user.profile');
    }
    return view('auth.login');
})->name('home');

Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::get('/registro', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return view('user.register');
})->name('user.register.view');

Route::post('/registro', [UserController::class, 'store'])->name('user.register');
Route::put('/usuario/{user}', [UserController::class, 'update'])->name('user.update');
Route::post('/temp-upload', [UserController::class, 'tempUpload'])->name('user.temp-upload');
Route::get('/temp-image/{filename}', [UserController::class, 'getTempImage'])->name('user.temp-image');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('is_admin')->resource('admin/users', AdminUserController::class);
    Route::get('/perfil', [UserController::class, 'profile'])->name('user.profile');
});