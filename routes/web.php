<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;

Route::get('/chat', [
    ChatController::class,
    'index'
]);


Route::post('/send-message', [
    ChatController::class,
    'send'
]);

Route::get('/notifications', [

    NotificationController::class,

    'index'

]);

Route::post('/notifications', [

    NotificationController::class,

    'store'

]);
