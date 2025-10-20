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

// Password reset routes
Route::get('/password/reset/{token}', [App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset.token');
Route::post('/password/reset', [App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.reset.update');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    /* Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard'); */

    Route::resource('admin/users', AdminUserController::class)->middleware('is_admin')->names('admin.users');
    Route::post('admin/users/{user}/generate-reset-link', [AdminUserController::class, 'generateResetLink'])->middleware('is_admin')->name('admin.users.generate-reset-link');
    Route::post('admin/users/{user}/assign-family', [App\Http\Controllers\Admin\FamilyController::class, 'assign'])->middleware('is_admin')->name('admin.users.assign-family');
    Route::delete('admin/users/{user}/remove-family', [App\Http\Controllers\Admin\FamilyController::class, 'remove'])->middleware('is_admin')->name('admin.users.remove-family');
    Route::get('admin/draw', [App\Http\Controllers\Admin\DrawController::class, 'index'])->middleware('is_admin')->name('admin.draw');
    Route::post('admin/draw/start', [App\Http\Controllers\Admin\DrawController::class, 'start'])->middleware('is_admin')->name('admin.draw.start');
    Route::get('/perfil', [UserController::class, 'profile'])->name('user.profile');
});