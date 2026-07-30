<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class, 'main'])->name('main');
Route::get('/project', [MainController::class, 'project'])->name('project');
Route::get('/author', [MainController::class, 'author'])->name('author');

Route::redirect(
    '/different/little-girl-pull-tablecloth',
    '/moment/little-girl-pull-tablecloth',
    301,
);

Route::redirect(
    '/different/about-soul',
    '/moment/about-soul',
    301,
);

Route::redirect(
    '/different/in-park',
    '/moment/in-park',
    301,
);

Route::get('/{section}/{slug}', [MainController::class, 'poem'])->name('poem');

Route::get('/{section}', [MainController::class, 'section'])->name('section');
