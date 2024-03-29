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

Route::get('terms-conditions', [App\Http\Controllers\FrontController::class, 'terms_condition']);

//Route::get('error_404', 'ErrorController@error404');
Route::get('error_404', [App\Http\Controllers\ErrorController::class, 'error404']);

/*
 * API routes
 * */
Route::get('service/email_check', [App\Http\Controllers\ApiController::class, 'email_check']);

Route::post('service/login', [App\Http\Controllers\ApiController::class, 'login']);
Route::post('service/other_login', [App\Http\Controllers\ApiController::class, 'otherLogin']);
Route::post('service/forget_password_request', [App\Http\Controllers\ApiController::class, 'forgetPasswordRequest']);
Route::post('service/reset_password_request', [App\Http\Controllers\ApiController::class, 'resetPasswordRequest']);
Route::post('service/reset_password_confirmation', [App\Http\Controllers\ApiController::class, 'resetPasswordConfirmation']);

Route::post('service/client/store', [App\Http\Controllers\ApiController::class, 'storeClient']);
Route::post('service/client/view', [App\Http\Controllers\ApiController::class, 'getClientDetails']);
Route::post('service/client/update', [App\Http\Controllers\ApiController::class, 'updateClient']);
Route::post('service/client/update_photo', [App\Http\Controllers\ApiController::class, 'updateClientPhoto']);
Route::post('service/client/resend_verification_code', [App\Http\Controllers\ApiController::class, 'resendVerificationCode']);
Route::post('service/client/verify', [App\Http\Controllers\ApiController::class, 'verifyClient']);
Route::post('service/client/change_type', [App\Http\Controllers\ApiController::class, 'updateClientType']);
Route::post('service/client/change_notification_setting', [App\Http\Controllers\ApiController::class, 'updateClientNotificationSetting']);

Route::post('service/genre/all', [App\Http\Controllers\ApiController::class, 'allGenre']);

Route::post('service/goal/all', [App\Http\Controllers\ApiController::class, 'allGoal']);
Route::post('service/goal/store', [App\Http\Controllers\ApiController::class, 'storeGoal']);
Route::post('service/goal/view', [App\Http\Controllers\ApiController::class, 'getGoalDetails']);
Route::post('service/goal/update', [App\Http\Controllers\ApiController::class, 'updateGoal']);
Route::post('service/goal/delete', [App\Http\Controllers\ApiController::class, 'deleteGoal']);
Route::post('service/goal/add_collaborators', [App\Http\Controllers\ApiController::class, 'addCollaborators']);
Route::post('service/goal/delete_collaborators', [App\Http\Controllers\ApiController::class, 'deleteCollaborators']);
Route::post('service/goal/make_complete', [App\Http\Controllers\ApiController::class, 'makeCompleteGoal']);

Route::post('service/goal_step/store', [App\Http\Controllers\ApiController::class, 'storeGoalStep']);
Route::post('service/goal_step/view', [App\Http\Controllers\ApiController::class, 'getGoalStepDetails']);
Route::post('service/goal_step/update', [App\Http\Controllers\ApiController::class, 'updateGoalStep']);
Route::post('service/goal_step/delete', [App\Http\Controllers\ApiController::class, 'deleteGoalStep']);
Route::post('service/goal_step/request_mark_off', [App\Http\Controllers\ApiController::class, 'requestGoalStepMarkOff']);
Route::post('service/goal_step/make_complete', [App\Http\Controllers\ApiController::class, 'makeCompleteGoalStep']);
Route::post('service/goal_step/delete_attachment', [App\Http\Controllers\ApiController::class, 'deleteStepAttachment']);
Route::post('service/goal_step/get_collaborative_steps', [App\Http\Controllers\ApiController::class, 'getCollaborativeStep']);

Route::post('service/search', [App\Http\Controllers\ApiController::class, 'search']);
Route::post('service/current_goal_search', [App\Http\Controllers\ApiController::class, 'currentGoalSearch']);
Route::post('service/collaborator_profile', [App\Http\Controllers\ApiController::class, 'getCollaboratorProfile']);
Route::post('service/trophies', [App\Http\Controllers\ApiController::class, 'getTrophies']);
Route::post('service/search_completed_goal', [App\Http\Controllers\ApiController::class, 'searchCompletedGoal']);

Route::post('service/notification/all', [App\Http\Controllers\ApiController::class, 'getNotifications']);
Route::post('service/notification/view', [App\Http\Controllers\ApiController::class, 'getNotificationDetails']);
Route::post('service/notification/update', [App\Http\Controllers\ApiController::class, 'updateNotification']);

Route::post('service/network/my_connection', [App\Http\Controllers\ApiController::class, 'getMyNetworkConnection']);
Route::post('service/network/request_connect', [App\Http\Controllers\ApiController::class, 'addNetworkConnection']);
Route::post('service/network/view_connection', [App\Http\Controllers\ApiController::class, 'viewNetworkConnection']);
Route::post('service/network/request_accept', [App\Http\Controllers\ApiController::class, 'acceptNetworkConnection']);
Route::post('service/network/request_decline', [App\Http\Controllers\ApiController::class, 'declineNetworkConnection']);
Route::post('service/network/remove_connection', [App\Http\Controllers\ApiController::class, 'removeNetworkConnection']);

Route::post('service/support/get_tickets', [App\Http\Controllers\ApiController::class, 'getTickets']);
Route::post('service/support/view_ticket', [App\Http\Controllers\ApiController::class, 'getTicketDetails']);
Route::post('service/support/save_ticket', [App\Http\Controllers\ApiController::class, 'storeSupportTicket']);
Route::post('service/support/save_ticket_reply', [App\Http\Controllers\ApiController::class, 'storeSupportTicketReply']);


Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::get('profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
Route::get('reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password');
Route::post('update_user', [App\Http\Controllers\UserController::class, 'update']);
Route::post('update_password', [App\Http\Controllers\UserController::class, 'updatePassword']);

Route::get('clients', [App\Http\Controllers\ClientController::class, 'index']);
Route::get('clients/create', [App\Http\Controllers\ClientController::class, 'create']);
Route::post('clients/store', [App\Http\Controllers\ClientController::class, 'store']);
Route::get('clients/{id}', [App\Http\Controllers\ClientController::class, 'edit']);
Route::post('clients/update', [App\Http\Controllers\ClientController::class, 'update']);
Route::post('clients/delete', [App\Http\Controllers\ClientController::class, 'delete']);

Route::get('users', [App\Http\Controllers\UserController::class, 'index']);
Route::get('users/create', [App\Http\Controllers\UserController::class, 'create']);
Route::post('users/store', [App\Http\Controllers\UserController::class, 'store']);
Route::get('users/{id}', [App\Http\Controllers\UserController::class, 'edit']);
Route::post('users/update', [App\Http\Controllers\UserController::class, 'update']);
Route::post('users/delete', [App\Http\Controllers\UserController::class, 'delete']);

Route::get('genres', [App\Http\Controllers\GenreController::class, 'index']);
Route::get('genres/create', [App\Http\Controllers\GenreController::class, 'create']);
Route::post('genres/store', [App\Http\Controllers\GenreController::class, 'store']);
Route::get('genres/{id}', [App\Http\Controllers\GenreController::class, 'edit']);
Route::post('genres/update', [App\Http\Controllers\GenreController::class, 'update']);
Route::post('genres/delete', [App\Http\Controllers\GenreController::class, 'delete']);

Route::get('general_settings', [App\Http\Controllers\SettingController::class, 'generalSetting']);
Route::post('general_settings/update', [App\Http\Controllers\SettingController::class, 'generalSettingUpdate']);

Route::get('pages/terms_condition', [App\Http\Controllers\PageController::class, 'termsCondition']);
Route::post('pages/update', [App\Http\Controllers\PageController::class, 'updatePageData']);

Route::get('support_tickets', [App\Http\Controllers\SupportController::class, 'tickets']);
Route::get('view_support_tickets/{id}', [App\Http\Controllers\SupportController::class, 'ticketDetails']);
Route::post('support_tickets/send_reply', [App\Http\Controllers\SupportController::class, 'storeTicketReply']);
Route::post('support_tickets/close', [App\Http\Controllers\SupportController::class, 'closeTicket']);
Route::post('support_tickets/delete', [App\Http\Controllers\SupportController::class, 'deleteTicket']);
