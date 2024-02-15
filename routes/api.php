<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\DayController;
use App\Http\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'
], function ($router) {

    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/check_phone', [AuthController::class, 'check_phone']);
        Route::post('/reset_password', [AuthController::class, 'reset_password']);
        Route::post('/activation', [AuthController::class, 'activation']);
        Route::post('/set_password', [AuthController::class, 'set_password']);
        Route::post('/login', [AuthController::class, 'login']);
        // Route::get('/get_activation_user/{hash}', [AuthController::class, 'get_activation_user']);
        // Route::post('/activate_user/{hash}', [AuthController::class, 'activate_user']);
        // Route::post('/accept_invitation/{hash}', [AuthController::class, 'accept_invitation']);
        // Route::post('/forgot_password', [AuthController::class, 'forgot_password']);
        // Route::post('/password_recovery', [AuthController::class, 'password_recovery']);
        // Route::get('/get_avatar/{avatar_file}', [AuthController::class, 'get_avatar']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/registration', [AuthController::class, 'registration']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/change_mode/{role_type_id}', [AuthController::class, 'change_mode']);
            Route::post('/change_language/{lang_tag}', [AuthController::class, 'change_language']);
            Route::post('/change_theme/{theme_slug}', [AuthController::class, 'change_theme']);
            Route::post('/change_location/{location_id}', [AuthController::class, 'change_location']);
            // Route::post('/update', [AuthController::class, 'update']);
            // Route::post('/upload_avatar', [AuthController::class, 'upload_avatar']);
            // Route::post('/delete_avatar', [AuthController::class, 'delete_avatar']);
            // Route::post('/change_password', [AuthController::class, 'change_password']);
            // Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::group([
        'prefix' => 'users'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/{user_id}', [UserController::class, 'get_user']);
        });
    });

    Route::group([
        'prefix' => 'cities'
    ], function ($router) {
        Route::get('/get', [CityController::class, 'get']);
        Route::get('/get/{city_id}', [CityController::class, 'get_city_by_id']);
        Route::post('/find_by_coordinates', [CityController::class, 'find_by_coordinates']);
        Route::post('/find_coordinates_by_city/{city_id}', [CityController::class, 'find_coordinates_by_city']);
    });

    Route::group([
        'prefix' => 'days'
    ], function ($router) {
        Route::get('/get_days_of_week', [DayController::class, 'get_days_of_week']);
    });

    Route::group([
        'prefix' => 'categories'
    ], function ($router) {
        Route::get('/get', [CategoryController::class, 'get']);
        Route::get('/get/{category_id}', [CategoryController::class, 'get_category']);
        Route::get('/get_image/{file_name}', [CategoryController::class, 'get_category_image']);
    });

    Route::group([
        'prefix' => 'partners'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get', [PartnerController::class, 'get']);
            Route::get('/my_organizations', [PartnerController::class, 'my_organizations']);
            Route::post('/submit_application', [PartnerController::class, 'submit_application']);
            Route::post('/accept_application', [PartnerController::class, 'accept_application']);
        });
    });

    Route::group([
        'prefix' => 'branches'
    ], function ($router) {
        Route::post('/get_branches', [BranchController::class, 'get_branches']);
        Route::get('/get_image/{file_name}', [BranchController::class, 'get_branch_image']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/my_branches', [BranchController::class, 'my_branches']);
            Route::get('/organization/{organization_id}', [BranchController::class, 'get_org_branches']);
            Route::post('/add', [BranchController::class, 'add']);
        });
    });

    Route::group([
        'prefix' => 'managers'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::get('/get', [ManagerController::class, 'get']);
            Route::post('/submit_application', [ManagerController::class, 'submit_application']);
            Route::post('/accept_application', [ManagerController::class, 'accept_application']);
        });
    });


    Route::group([
        'prefix' => 'services'
    ], function ($router) {
        Route::post('/get', [ServiceController::class, 'get']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/add', [ServiceController::class, 'add']);
            Route::get('/my_services', [ServiceController::class, 'my_services']);
        });

    });

    Route::group([
        'prefix' => 'operations'
    ], function ($router) {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/create', [OperationController::class, 'create']);
            Route::get('/check/{hash}', [OperationController::class, 'check']);
            Route::post('/scan/{hash}', [OperationController::class, 'scan']);
        });
    });

    Route::group([
        'prefix' => 'stocks'
    ], function ($router) {
        Route::get('/get/{current_location_id}', [StockController::class, 'get']);
        Route::get('/get_image/{stock_id}', [StockController::class, 'get_stock_image']);

        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/create', [StockController::class, 'create']);
            Route::post('/view/{stock_id}', [StockController::class, 'view']);
            Route::post('/share/{stock_id}', [StockController::class, 'share']);
        });
    });
});