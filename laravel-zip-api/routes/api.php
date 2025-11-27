<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


use App\Http\Controllers\PostalCodeController;

Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{id}', [PostalCodeController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{id}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{id}', [PostalCodeController::class, 'destroy']);
});
