<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::prefix('/menu')->middleware(['auth'])->group(function(){
    Route::get('/', [MenuController::class, 'list'])->name('menu.list');
    Route::get('/data', [MenuController::class, 'data'])->name('menu.data');
    Route::get('/add', [MenuController::class, 'add'])->name('menu.add');
    Route::get('/edit/{menuId}', [MenuController::class, 'editMenu'])->name('menu.edit');
    Route::post('/update/{menuId}', [MenuController::class, 'updateMenu'])->name('menu.update');
    Route::post('/delete/{menuId}', [MenuController::class, 'deleteMenu'])->name('menu.delete');
    Route::post('/insert', [MenuController::class, 'insert'])->name('menu.insert_new');
});

Route::prefix('/user')->middleware(['auth'])->group(function(){
    Route::get('/', [UserController::class, 'list'])->name('user.list');
    Route::get('/data', [UserController::class, 'data'])->name('user.data');
    Route::get('/edit/{userId}', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/update/{userId}', [UserController::class, 'update'])->name('user.update');
});

