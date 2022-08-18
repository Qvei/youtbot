<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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

Route::get('/', function (Request $request) {
	// $value = $request->session()->all();
	// dd($value);
    return view('welcome');
});

Route::any('/telegrams', [\App\Http\Controllers\TeleController::class, 'get_data_from_tg']);