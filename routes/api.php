<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//ONLY ADMIN CAN ACCESS THESE ROUTES
Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
    Route::post('create/user', [UserController::class, 'createUser']);
    Route::put('update/user/details/{id}', [UserController::class, 'updateUserDetails']);
    Route::delete('delete/user/{id}', [UserController::class, 'deleteUser']);
    Route::get('user/list', [UserController::class, 'usersList']);
    Route::post('product/create', [ProductController::class, 'createProduct']);
    Route::put('/product/update/{prodId}', [ProductController::class, 'updateProduct']);
    Route::delete('/product/remove/{prodId}', [ProductController::class, 'removeProduct']);
    Route::post('/assign/driver', [DeliveryController::class, 'assignDriver']);
});
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/product/list', [ProductController::class, 'productList']);
    Route::post('user/logout',[AuthController::class, 'logout']);
}); 
//ONLY CUSTOMER CAN ACCESS THESE ROUTES
Route::middleware(['auth:sanctum', 'role:customer'])->group(function(){
    Route::post('/add/product/to/cart', [CartController::class, 'addToCart']);
    Route::get('/view/cart', [CartController::class, 'viewCart']);
    Route::delete('/remove/to/cart/{cartItemId}', [CartController::class, 'removeCart']);
    Route::post('/order/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/customer/order', [OrderController::class, 'myOrder']);
    Route::post('customer/receipt', [ReportController::class, 'customerReceipt']);
});

//ONLY DRIVER CAN ACCESS THESE ROUTES
Route::middleware(['auth:sanctum', 'role:driver'])->group(function(){
    Route::post('/deliver/order', [DeliveryController::class, 'deliverOrder']);
    Route::post('/driver/orders', [DeliveryController::class, 'driverOrder']);
});
//ONLY MANAGER CAN ACCESS THESE ROUTES
Route::middleware(['auth:sanctum', 'role:manager'])->group(function(){
    Route::get('/product/list', [ReportController::class, 'productList']);
});

Route::post('user/login', [AuthController::class, 'login']);
Route::put('reset/user/password', [AuthController::class, 'resetPassword']);