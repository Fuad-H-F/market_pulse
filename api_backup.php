<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HashTagController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HitCountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('delete-user', [AuthController::class, 'deleteUser'])->name('delete-user');
    Route::post('create_level_one', [UserController::class, 'levelOne']);
    Route::post('create_level_two', [UserController::class, 'levelTwo'])->name('create_level_two');
    Route::post('create_level_three', [UserController::class, 'levelThree']);
    Route::post('update_user', [AuthController::class, 'update_user']);
    Route::get('all_users/{level?}', [UserController::class, 'get_all_users']);
    Route::get('get_users_by_type/{level}', [UserController::class, 'get_users_by_type']);
    Route::get('get_user_details/{id}', [UserController::class, 'get_user_details']);
    Route::post('save_gps_location', [MapController::class, 'saveGpsLocation']);
    Route::get('get_locations/{user_id}/{start_date}/{end_date}/{hash_tag?}', [MapController::class, 'getLocations']);
    Route::post('get_multi_locations', [MapController::class, 'getMultiLocations']);
    Route::get('generate_report/{user_id}/{start_date}/{end_date}/{hash_tag?}', [MapController::class, 'generateReport']);
    Route::get('in_out_report/{start_date}/{end_date}/{userId}', [UserController::class, 'inOutReport']);
    Route::get('hit_counts/{from}/{to}/{userId}', [HitCountController::class, 'getHitCounts']);
    Route::post('update_hit', [HitCountController::class, 'updateHitCount']);

    Route::get('get_hashtags', [MapController::class, 'getHashtags']);
    Route::post('create_tag', [HashTagController::class, 'createHashTag']);
    Route::get('all_tags', [HashTagController::class, 'getHashTags']);
});
