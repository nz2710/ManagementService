<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DepotController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\VehicleController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group([
    'middleware' => ['api'],
	'namespace' => '',
], function ($router) {

    Route::group([
        // 'middleware' => ['auth:sanctum'],
        'namespace' => 'Admin',
        'prefix' => 'admin'
    ], function ($router) {
        //Depot routes
        Route::get('depot', [DepotController::class, 'index']);
        Route::post('depot', [DepotController::class, 'store']);
        Route::get('depot/{id}', [DepotController::class, 'show']);
        Route::put('depot/{id}', [DepotController::class, 'update']);
        Route::delete('depot/{id}', [DepotController::class, 'destroy']);

        //Order routes
        Route::get('order', [OrderController::class, 'index']);
        Route::post('order', [OrderController::class, 'store']);
        Route::get('order/{id}', [OrderController::class, 'show']);
        Route::put('order/{id}', [OrderController::class, 'update']);
        Route::delete('order/{id}', [OrderController::class, 'destroy']);

        //Partner routes
        Route::get('partner', [PartnerController::class, 'index']);
        Route::post('partner', [PartnerController::class, 'store']);
        Route::get('partner/{id}', [PartnerController::class, 'show']);
        Route::put('partner/{id}', [PartnerController::class, 'update']);
        Route::delete('partner/{id}', [PartnerController::class, 'destroy']);

        //Vehicle routes
        Route::get('vehicle', [VehicleController::class, 'index']);
        Route::post('vehicle', [VehicleController::class, 'store']);
        Route::get('vehicle/{id}', [VehicleController::class, 'show']);
        Route::put('vehicle/{id}', [VehicleController::class, 'update']);
        Route::delete('vehicle/{id}', [VehicleController::class, 'destroy']);
    });
});
