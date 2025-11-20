<?php

use App\Http\Controllers\Admin\DrawController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\FamilyGroupController;
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

// Rutas de registro customizadas con middleware
Route::get('/registro', function () {
    if (Auth::check()) {
        return redirect()->route('user.profile');
    }
    return view('user.register');
})->middleware('capture.family.group')->name('user.register.view');

Route::post('/registro', [UserController::class, 'store'])->name('user.register');
Route::put('/usuario/{user}', [UserController::class, 'update'])->name('user.update');
Route::post('/temp-upload', [UserController::class, 'tempUpload'])->name('user.temp-upload');
Route::get('/temp-image/{filename}', [UserController::class, 'getTempImage'])->name('user.temp-image');
Route::post('/temp-upload-gift/{index}', [UserController::class, 'tempUploadGift'])->name('user.temp-upload-gift');
Route::get('/temp-image-gift/{filename}', [UserController::class, 'getTempImageGift'])->name('user.temp-image-gift');

// Password reset routes
Route::get('/password/reset/{token}', [App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset.token');
Route::post('/password/reset', [App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.reset.update');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::resource('admin/users', AdminUserController::class)->middleware('is_admin')->names('admin.users');
    Route::get('admin/users/{user}/profile', [AdminUserController::class, 'showProfile'])->middleware('is_admin')->name('admin.users.profile');
    Route::post('admin/users/{user}/generate-reset-link', [AdminUserController::class, 'generateResetLink'])->middleware('is_admin')->name('admin.users.generate-reset-link');
    Route::post('admin/users/{user}/assign-family', [FamilyController::class, 'assign'])->middleware('is_admin')->name('admin.users.assign-family');
    Route::delete('admin/users/{user}/remove-family', [FamilyController::class, 'remove'])->middleware('is_admin')->name('admin.users.remove-family');
    
    // Rutas de gestión de family groups
    Route::resource('admin/family-groups', FamilyGroupController::class)->middleware('is_admin')->names('admin.family-groups');
    
    Route::get('admin/draw', [DrawController::class, 'index'])->middleware('is_admin')->name('admin.draw');
    Route::post('admin/draw/start', [DrawController::class, 'start'])->middleware('is_admin')->name('admin.draw.start');
    Route::post('admin/draw/reset', [DrawController::class, 'reset'])->middleware('is_admin')->name('admin.draw.reset');
    Route::get('/perfil', [UserController::class, 'profile'])->name('user.profile');
});