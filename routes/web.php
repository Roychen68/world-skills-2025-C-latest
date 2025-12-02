<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\path;

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

Route::get('/',[path::class,"listen"]);
Route::get('/favorite',[path::class,"favorite"]);
Route::get('/heritages/{path?}',[path::class,"listen"])->where("path",".*");
Route::get('/tags/{tag?}',[path::class,"tag"])->where("tag",".*");
Route::get('/like',[path::class,"like"])->name("tag");
Route::get('/search',[path::class,"search"])->name("tag");
