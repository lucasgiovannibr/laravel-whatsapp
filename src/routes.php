<?php

use Illuminate\Support\Facades\Route;
use DesterroWhatsApp\Http\Controllers\WebhookController;

Route::group([
    'prefix' => config('whatsapp.route_prefix', 'whatsapp'),
    'middleware' => config('whatsapp.route_middleware', ['api']),
], function () {
    // Rota para receber webhooks
    Route::post('/webhook', [WebhookController::class, 'handleWebhook']);
}); 