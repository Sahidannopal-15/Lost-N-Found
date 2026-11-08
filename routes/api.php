<?php

use App\Http\Controllers\Api\ItemController;
use Illuminate\Support\Facades\Route;

Route::get('/items', [ItemController::class, 'index'])->name('api.items.index');
Route::get('/items/{id}', [ItemController::class, 'show'])
    ->whereNumber('id')
    ->name('api.items.show');
