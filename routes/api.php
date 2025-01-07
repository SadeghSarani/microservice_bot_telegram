<?php

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::prefix('chat')->controller(ChatBotController::class)->group(function () {
        Route::post('/', 'chatCreate');
    });

    Route::prefix('/service')->controller(ServiceController::class)->group(function () {
        Route::post('/', 'serviceCreate');
        Route::get('/', 'serviceList');
        Route::get('/{id}', 'serviceShow');
    });

    Route::prefix('prompt')->controller(PromptController::class)->group(function () {
        Route::post('/', 'promptCreate');
        Route::get('/', 'promptList');
        Route::get('/{id}', 'promptShow');
    });
});