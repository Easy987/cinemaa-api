<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaseController;
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
Route::get('/movie/{slug}/{year}/{length?}', [BaseController::class, 'view'])->name('view');
Route::get('/film/{slug}/{year}/{length?}', [BaseController::class, 'view'])->name('view');

Route::get('/', [BaseController::class, 'empty']);
Route::get('/{uuid}/{lang}/{movie_id}/', [BaseController::class, 'index']);
Route::get('/{link_id}', [BaseController::class, 'link'])->name('link');
Route::post('/{uuid}/{lang}/{movie_id}/report/{link_id}', [BaseController::class, 'report'])->name('report');

Route::get('/share/{lang}/{slug}/{year}/{length}', [BaseController::class, 'share'])->name('share');

