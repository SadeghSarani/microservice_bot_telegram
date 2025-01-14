<?php

use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::prefix('chat')->controller(ChatBotController::class)->group(function () {
        Route::post('/', 'chatWithoutTelegram');
    });

    Route::prefix('/service')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'serviceList');
        Route::post('/', 'serviceCreate');
        Route::get('/{id}', 'serviceShow');
        Route::post('/button', 'createButtonTelegram');
    });

    Route::prefix('prompt')->controller(PromptController::class)->group(function () {
        Route::get('/', 'promptList');
        Route::post('/', 'promptCreate');
        Route::get('/{id}', 'promptShow');
    });

});

Route::any('/webhook', [TelegramController::class,'webHook']);
