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

/*Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');*/

/*
 * User auth route
 * */
//Route::get('registration', 'RegistrationController@index');
//Route::post('registration/store', 'RegistrationController@store');
Route::get('registration', [App\Http\Controllers\RegistrationController::class,'index']);
Route::post('registration/store', [App\Http\Controllers\RegistrationController::class,'store']);

//Route::get('login', 'AuthenticationController@login')->name('login');
Route::get('login', [App\Http\Controllers\AuthenticationController::class, 'login'])->name('login');
//Route::post('post_login', 'AuthenticationController@postLogin');
Route::post('post_login', [App\Http\Controllers\AuthenticationController::class, 'postLogin']);
Route::get('logout', 'AuthenticationController@logout');
Route::get('logout', [App\Http\Controllers\AuthenticationController::class, 'logout']);
//Route::get('error_404', 'ErrorController@error404');
Route::get('error_404', [App\Http\Controllers\ErrorController::class, 'error404']);

/*
 * API routes
 * */
Route::post('service/login', [App\Http\Controllers\ApiController::class, 'login']);

Route::post('service/client/store', [App\Http\Controllers\ApiController::class, 'storeClient']);
Route::post('service/client/view', [App\Http\Controllers\ApiController::class, 'getClientDetails']);
Route::post('service/client/update', [App\Http\Controllers\ApiController::class, 'updateClient']);


Auth::routes();

//Route::get('/', 'HomeController@index');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
//Route::get('/home', 'Customer\HomeController@index')->name('home');
Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::get('/dashboard', 'HomeController@index')->name('dashboard');
Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
//Route::get('profile', 'UserController@profile')->name('profile');
//Route::get('reset-password', 'UserController@resetPassword')->name('reset-password');
//Route::post('update_user', 'UserController@update');
//Route::post('update_password', 'UserController@updatePassword');
Route::get('profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
Route::get('reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password');
Route::post('update_user', [App\Http\Controllers\UserController::class, 'update']);
Route::post('update_password', [App\Http\Controllers\UserController::class, 'updatePassword']);

/*Route::get('clients', 'ClientController@index');
Rote::get('clients/create', 'ClientController@create');
Route::post('clients/store', 'ClientController@store');
Route::get('clients/{id}', 'ClientController@edit');
Route::post('clients/update', 'ClientController@update');
Route::post('clients/delete', 'ClientController@delete');*/
Route::get('clients', [App\Http\Controllers\ClientController::class, 'index']);
Route::get('clients/create', [App\Http\Controllers\ClientController::class, 'create']);
Route::post('clients/store', [App\Http\Controllers\ClientController::class, 'store']);
Route::get('clients/{id}', [App\Http\Controllers\ClientController::class, 'edit']);
Route::post('clients/update', [App\Http\Controllers\ClientController::class, 'update']);
Route::post('clients/delete', [App\Http\Controllers\ClientController::class, 'delete']);

/*Route::get('users', 'UserController@index');
Route::get('users/create', 'UserController@create');
Route::post('users/store', 'UserController@store');
Route::get('users/{id}', 'UserController@edit');
Route::post('users/update', 'UserController@update');
Route::post('users/delete', 'UserController@delete');*/
Route::get('users', [App\Http\Controllers\UserController::class, 'index']);
Route::get('users/create', [App\Http\Controllers\UserController::class, 'create']);
Route::post('users/store', [App\Http\Controllers\UserController::class, 'store']);
Route::get('users/{id}', [App\Http\Controllers\UserController::class, 'edit']);
Route::post('users/update', [App\Http\Controllers\UserController::class, 'update']);
Route::post('users/delete', [App\Http\Controllers\UserController::class, 'delete']);

//Route::get('general_settings', 'SettingController@generalSetting');
//Route::post('general_settings/update', 'SettingController@generalSettingUpdate');
Route::get('general_settings', [App\Http\Controllers\SettingController::class, 'generalSetting']);
Route::post('general_settings/update', [App\Http\Controllers\SettingController::class, 'generalSettingUpdate']);
