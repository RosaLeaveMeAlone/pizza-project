<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\OrderController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
