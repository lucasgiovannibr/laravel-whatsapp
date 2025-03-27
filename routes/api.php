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
        Route::post('/send-media', [WhatsAppController::class, 'sendMedia']);
        Route::post('/send-list', [WhatsAppController::class, 'sendList']);
        Route::post('/send-button', [WhatsAppController::class, 'sendButton']);
        
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
    });
});

// Rota para webhooks (não autenticada)
Route::post("/$prefix/webhook", [WebhookController::class, 'handleWebhook'])
    ->middleware(config('whatsapp.webhook.middleware', [])); 