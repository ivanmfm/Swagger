<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Auth\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Public routes of authtication
Route::controller(AuthController::class)->group(function() {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Public routes of product
// Route::controller(BlogController::class)->group(function() {
//     Route::get('/blog', 'index');
//     Route::get('/blog/{id}', 'show');
//     Route::get('/blog/search/{title}', 'search');
// });

// Protected routes of product and logout
// Route::middleware('auth:api')->group( function () {
Route::middleware('auth:api')->group( function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::controller(BlogController::class)->group(function() {
        Route::get('/blog', 'index');
        Route::get('/blog/{id}', 'show');
        Route::get('/blog/search/{title}', 'search');
        Route::post('/blog', 'store');
        Route::post('/blog/{id}', 'update');
        Route::delete('/blog/{id}', 'destroy');
    });
});