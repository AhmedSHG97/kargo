<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\AdvertisementController;
use App\Http\Controllers\Api\Admin\AdditionalServiceController;
use App\Http\Controllers\Api\Admin\DeliveryServiceController;
use App\Http\Controllers\Api\Admin\OtherServiceController;
use App\Http\Controllers\Api\Admin\TypeOptionController;
use App\Http\Controllers\Api\Admin\ShipmentTypeController;
use App\Http\Controllers\Api\Auth\UserAuthenticationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Models\OtherService;
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
Route::post('/login',[UserAuthenticationController::class,"login"]);
Route::post("social/login", [UserAuthenticationController::class, 'socialLogin']);
Route::post('/register',[UserAuthenticationController::class,"register"]);
Route::post('/resendVerification',[UserAuthenticationController::class,"resendVerification"]);
Route::post('/verify',[UserAuthenticationController::class,"verifyUser"]);
Route::post('refresh/token',[UserAuthenticationController::class,"refreshToken"]);
Route::post('/reset/password',[UserAuthenticationController::class,"resetPassword"]);
Route::post('/change/password',[UserAuthenticationController::class,"changePassword"]);

Route::group(['middleware'=>["auth:api"]],function(){
    Route::post('/password/update',[UserAuthenticationController::class,"updatePassword"]);
    Route::group(['prefix' => "me"],function(){
        Route::get('info',[UserController::class, "getUserInfo"]);

        Route::post('info/update',[UserController::class, "infoUpdate"]);
    });

    Route::get("adds",[UserController::class,"getAdds"]);
    Route::get("shipment/types",[UserController::class,"getShippmentTypes"]);
    Route::get("additional/services",[UserController::class,"getAdditionalServices"]);
    Route::get("delivery/services",[UserController::class,"getDeliveryServices"]);
    Route::get("other/services",[UserController::class,"getOtherServices"]);
    Route::get("countries",[UserController::class,"getCountries"]);
    Route::get("states/{country_id}",[UserController::class,"getStates"]);
    Route::get("counties/{state_id}",[UserController::class,"getCounties"]);
    Route::get("address",[AddressController::class,"getAddresses"]);
    Route::post("address",[AddressController::class,"create"]);
    Route::post("address/update",[AddressController::class,"update"]);
    Route::get("address/{address_id}",[AddressController::class,"show"]);
    Route::delete("address/delete/{address_id}",[AddressController::class,"destroy"]);
    Route::get("orders",[OrderController::class,"getOrders"]);
    Route::delete("order/delete/{order_id}",[OrderController::class,"deleteOrder"]);
    Route::get("order/{order_id}",[OrderController::class,"showOrder"]);
    Route::post("order/create",[OrderController::class,"makeOrder"]);
    Route::group(["prefix" => "admin"],function(){
        Route::apiResource("shipment",ShipmentTypeController::class);
        Route::apiResource("shipment/type/option",TypeOptionController::class);
        Route::apiResource("additional/service",AdditionalServiceController::class);
        Route::apiResource("delivery/service",DeliveryServiceController::class,["as" => "delivery"]);
        Route::apiResource("other/service",OtherServiceController::class,["as" => "other"]);
        Route::apiResource("advertisement",AdvertisementController::class);
    });
});
