<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;

Route::get('/', function () {
    return redirect()->route('books.index');
});

/***
 * Books Resource Controller
 * Only show index and show for time being
 * WIP
 */
Route::resource('books', BookController::class)
    ->only(['index', 'show']);


/***
 * Reviews Route
 */
Route::resource('books.reviews', ReviewController::class)
    ->scoped(['review' => 'book'])
    ->only(['create', 'store']);
