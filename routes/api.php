<?php

use DesterroShop\LaravelWhatsApp\Http\Controllers\AuthController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\CircuitBreakerController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\TransactionController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\WebhookController;
use DesterroShop\LaravelWhatsApp\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas para o pacote Laravel WhatsApp
|
*/

// Define o prefixo para as rotas com base na configuração
$prefix = config('whatsapp.route_prefix', 'api/whatsapp');

// Middleware para as rotas Web e API
$webMiddleware = config('whatsapp.middleware.web', ['web']);
$apiMiddleware = config('whatsapp.middleware.api', ['api']);

// Rotas da API autenticadas
Route::group(['prefix' => $prefix, 'middleware' => $apiMiddleware], function () {
    // Rotas de autenticação
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    
    // Rotas protegidas por autenticação
    Route::middleware('auth:sanctum')->group(function () {
        // Status
        Route::get('/status', [WhatsAppController::class, 'status']);
        
        // Enviar mensagens
        Route::post('/send-text', [WhatsAppController::class, 'sendText']);
        Route::post('/send-template', [WhatsAppController::class, 'sendTemplate']);
        Route::post('/send-image', [WhatsAppController::class, 'sendImage']);
        Route::post('/send-file', [WhatsAppController::class, 'sendFile']);
        Route::post('/send-audio', [WhatsAppController::class, 'sendAudio']);
        Route::post('/send-video', [WhatsAppController::class, 'sendVideo']);
        Route::post('/send-media', [WhatsAppController::class, 'sendMedia']);
        Route::post('/send-location', [WhatsAppController::class, 'sendLocation']);
        Route::post('/send-contact', [WhatsAppController::class, 'sendContact']);
        Route::post('/send-buttons', [WhatsAppController::class, 'sendButtons']);
        Route::post('/send-list', [WhatsAppController::class, 'sendList']);
        Route::post('/send-poll', [WhatsAppController::class, 'sendPoll']);
        Route::post('/send-product', [WhatsAppController::class, 'sendProduct']);
        Route::post('/send-catalog', [WhatsAppController::class, 'sendCatalog']);
        Route::post('/send-order', [WhatsAppController::class, 'sendOrder']);
        Route::post('/send-reaction', [WhatsAppController::class, 'sendReaction']);
        Route::post('/send-sticker', [WhatsAppController::class, 'sendSticker']);
        
        // Agendamento de mensagens
        Route::post('/schedule-message', [WhatsAppController::class, 'scheduleMessage']);
        Route::delete('/schedule-message/{id}', [WhatsAppController::class, 'cancelScheduledMessage']);
        
        // Gerenciamento de sessões
        Route::get('/sessions', [WhatsAppController::class, 'getSessions']);
        Route::post('/sessions', [WhatsAppController::class, 'createSession']);
        Route::get('/sessions/{id}', [WhatsAppController::class, 'getSessionStatus']);
        Route::get('/sessions/{id}/qr', [WhatsAppController::class, 'getQrCode']);
        Route::delete('/sessions/{id}', [WhatsAppController::class, 'deleteSession']);
        
        // Histórico de mensagens
        Route::get('/messages/{number}', [WhatsAppController::class, 'getMessages']);
        
        // Gerenciamento de webhooks
        Route::get('/webhooks', [WebhookController::class, 'index']);
        Route::post('/webhooks', [WebhookController::class, 'store']);
        Route::delete('/webhooks/{url}', [WebhookController::class, 'destroy']);
        
        // Circuit Breaker
        Route::get('/circuit-breaker', [CircuitBreakerController::class, 'index']);
        Route::get('/circuit-breaker/{service}', [CircuitBreakerController::class, 'show']);
        Route::post('/circuit-breaker/{service}/reset', [CircuitBreakerController::class, 'reset']);
        
        // Transações
        Route::post('/transaction/begin', [TransactionController::class, 'begin']);
        Route::post('/transaction/commit', [TransactionController::class, 'commit']);
        Route::post('/transaction/rollback', [TransactionController::class, 'rollback']);
        Route::get('/transaction/{id}', [TransactionController::class, 'status']);
        
        // Logs por ID de correlação
        Route::get('/logs/correlation/{id}', [WhatsAppController::class, 'getLogsByCorrelationId']);
    });
});

// Rota para webhooks (não autenticada)
Route::post("/$prefix/webhook", [WebhookController::class, 'handleWebhook'])
    ->middleware(config('whatsapp.webhook.middleware', [])); 