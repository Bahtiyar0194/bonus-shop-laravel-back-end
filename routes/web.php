<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StockController;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

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


Route::get('setlocale/{locale}', function($lang){
	\Session::put('lang', $lang);
	return redirect()->back();   
});

Route::group(['middleware'=>'language'],function ()
{

	Route::get('/get_app', function(){
		return view('get_app');
	});

	Route::prefix('stock')->group(function() {
		Route::get('/{shared_stock}', [StockController::class, 'get_shared_stock']);
	});

	Route::prefix('auth')->group(function() {
		Route::get('register/{login}', [AuthController::class, 'register']);
		Route::post('register_form', [AuthController::class, 'register_form'])->name('validate.register_form');
	});
});