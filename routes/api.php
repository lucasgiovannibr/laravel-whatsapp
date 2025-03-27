<?php

use DesterroShop\LaravelWhatsApp\Http\Controllers\SessionController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\MessageController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\TemplateController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas para o pacote Laravel WhatsApp
|
*/

// Grupo de rotas protegidas por middleware de API
Route::prefix('api/whatsapp')
    ->middleware(config('whatsapp.middleware.api', ['api']))
    ->group(function () {
        // Rotas para gerenciamento de sessões
        Route::apiResource('sessions', SessionController::class);
        Route::get('sessions/{id}/status', [SessionController::class, 'status']);
        Route::get('sessions/{id}/qr', [SessionController::class, 'qrCode']);
        
        // Rotas para mensagens
        Route::post('send', [MessageController::class, 'sendText']);
        Route::post('send-template', [MessageController::class, 'sendTemplate']);
        Route::post('send-media', [MessageController::class, 'sendMedia']);
        Route::post('send-location', [MessageController::class, 'sendLocation']);
        Route::post('send-contact', [MessageController::class, 'sendContact']);
        Route::post('send-buttons', [MessageController::class, 'sendButtons']);
        Route::get('messages', [MessageController::class, 'getMessages']);
        
        // Rotas para templates
        Route::apiResource('templates', TemplateController::class);
        Route::post('templates/{name}/render', [TemplateController::class, 'renderTemplate']);
    });

// Rota para webhook (geralmente não protegida por autenticação padrão)
Route::post('webhook/whatsapp', [WebhookController::class, 'handle'])
    ->middleware(config('whatsapp.middleware.webhook', []))
    ->name('whatsapp.webhook'); 