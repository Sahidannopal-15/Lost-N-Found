<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/create', [ItemController::class, 'create'])->name('items.create')->middleware(['auth', 'role:pengguna,petugas']);
Route::post('/items', [ItemController::class, 'store'])->name('items.store')->middleware(['auth', 'role:pengguna,petugas']);
Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit')->middleware('auth');
Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update')->middleware('auth');
Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy')->middleware('auth');
Route::post('/items/{item}/selesai', [ItemController::class, 'selesai'])->name('items.selesai')->middleware('auth');
Route::get('/items/{item}', [ItemController::class, 'show'])
    ->whereNumber('item')
    ->name('items.show');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/riwayat', [\App\Http\Controllers\RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/items/{item}/claims/create', [\App\Http\Controllers\ClaimController::class, 'create'])->name('claims.create');
    Route::post('/items/{item}/claims', [\App\Http\Controllers\ClaimController::class, 'store'])->name('claims.store');
});

Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->group(function (): void {
    Route::get('/claims', [\App\Http\Controllers\Petugas\ClaimController::class, 'index'])->name('petugas.claims.index');
    Route::post('/claims/{claim}/approve', [\App\Http\Controllers\Petugas\ClaimController::class, 'approve'])->name('petugas.claims.approve');
    Route::post('/claims/{claim}/reject', [\App\Http\Controllers\Petugas\ClaimController::class, 'reject'])->name('petugas.claims.reject');
});
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::resource('categories', AdminCategoryController::class)->names('admin.categories');
    Route::resource('locations', AdminLocationController::class)->names('admin.locations');
});
