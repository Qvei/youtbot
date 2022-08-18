<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Session;

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
	$value = $request->session()->get('key');
	dd($value);
    return view('welcome', compact('value'));
});

Route::post('/telegramsecret', [\App\Http\Controllers\TeleController::class, 'get_data_from_tg']);