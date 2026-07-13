<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class, 'main'])->name('main');
Route::get('/project', [MainController::class, 'project'])->name('project');
Route::get('/author', [MainController::class, 'author'])->name('author');

Route::get('/{parent}/{title}', [MainController::class, 'poem'])
    ->whereIn('parent', ['different', 'semicolon', 'text', 'moment'])
    ->name('poem');

Route::get('/{parent}', [MainController::class, 'section'])
    ->whereIn('parent', ['different', 'semicolon', 'text', 'moment'])
    ->name('section');
