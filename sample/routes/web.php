<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/tasks', [App\Http\Controllers\TodoList\TodoListController::class, 'index'])->name('tasks.index')->middleware('auth');
Route::post('/tasks', [App\Http\Controllers\TodoList\TodoListController::class, 'create'])->name('tasks.create')->middleware('auth');
Route::put('/tasks/{task}', [App\Http\Controllers\TodoList\TodoListController::class, 'update'])->name('tasks.update')->middleware('auth');
Route::delete('/tasks/{task}', [App\Http\Controllers\TodoList\TodoListController::class, 'delete'])->name('tasks.delete')->middleware('auth');