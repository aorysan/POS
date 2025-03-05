<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\LevelController;

// Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/', function() {
    return view('welcome'); 
});

Route::get('/level', [LevelController::class, 'index']);
Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/user', [UserController::class, 'index']);

Route::prefix('category')->group(function () {
    Route::get('/food-beverage', [ProductController::class, 'foodBeverage'])->name('products.food-beverage');
    Route::get('/beauty-health', [ProductController::class, 'beautyHealth'])->name('products.beauty-health');
    Route::get('/home-care', [ProductController::class, 'homeCare'])->name('products.home-care');
    Route::get('/baby-kid', [ProductController::class, 'babyKid'])->name('products.baby-kid');
});

Route::get('/user/{id}/name/{name}', [UserController::class, 'profile'])->name('user.profile');

Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');

Route::get('/user/tambah', [UserController::class, 'tambah']);
Route::post('/user/tambah_simpan', [UserController::class, 'tambah_simpan']);
Route::get('/user/ubah/{id}', [UserController::class, 'ubah']);
Route::post('/user/ubah_simpan/{id}', [UserController::class, 'ubah_simpan']);
Route::get('/user/hapus/{id}', [UserController::class, 'hapus']);