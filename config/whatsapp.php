<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações de URL da API
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à conexão com a API WhatsApp DesterroShop
    |
    */
    'api_url' => env('WHATSAPP_API_URL', 'http://localhost:8787'),
    'api_token' => env('WHATSAPP_API_TOKEN', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Sessão
    |--------------------------------------------------------------------------
    |
    | Defina as configurações para as sessões do WhatsApp
    |
    */
    'default_session' => env('WHATSAPP_DEFAULT_SESSION', 'default'),
    'qr_timeout' => env('WHATSAPP_QR_TIMEOUT', 60), // Tempo em segundos para expirar o QR code
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Webhook
    |--------------------------------------------------------------------------
    |
    | Configurações para o webhook que recebe eventos do WhatsApp
    |
    */
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL', null),
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET', null),
    'webhook_events' => [
        'message', 
        'message_ack', 
        'message_create', 
        'message_revoke_everyone', 
        'qr', 
        'disconnected', 
        'ready'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Media
    |--------------------------------------------------------------------------
    |
    | Configurações para armazenamento de mídia
    |
    */
    'media_storage' => env('WHATSAPP_MEDIA_STORAGE', 'local'), // local, s3
    'media_path' => env('WHATSAPP_MEDIA_PATH', 'whatsapp'),
    'media_disk' => env('WHATSAPP_MEDIA_DISK', 'public'), // Nome do disco de armazenamento do Laravel
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Templates
    |--------------------------------------------------------------------------
    |
    | Configurações para templates de mensagens
    |
    */
    'templates_path' => env('WHATSAPP_TEMPLATES_PATH', resource_path('views/whatsapp/templates')),
    'cache_templates' => env('WHATSAPP_CACHE_TEMPLATES', true),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Eventos
    |--------------------------------------------------------------------------
    |
    | Configurações para eventos do Laravel
    |
    */
    'broadcast_events' => env('WHATSAPP_BROADCAST_EVENTS', true),
    'broadcast_channel' => env('WHATSAPP_BROADCAST_CHANNEL', 'whatsapp'),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Logs
    |--------------------------------------------------------------------------
    |
    | Configurações para logging
    |
    */
    'log_channel' => env('WHATSAPP_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
    'log_level' => env('WHATSAPP_LOG_LEVEL', 'debug'),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações Avançadas
    |--------------------------------------------------------------------------
    |
    | Configurações avançadas para o cliente WhatsApp
    |
    */
    'request_timeout' => env('WHATSAPP_REQUEST_TIMEOUT', 30), // Timeout em segundos
    'retry_attempts' => env('WHATSAPP_RETRY_ATTEMPTS', 3),
    'auto_reconnect' => env('WHATSAPP_AUTO_RECONNECT', true),
    
    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware para rotas do WhatsApp
    |
    */
    'middleware' => [
        'web' => ['web'],
        'api' => ['api', 'auth:sanctum'],
        'webhook' => [],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Proxy
    |--------------------------------------------------------------------------
    |
    | Em alguns ambientes você pode precisar de um proxy para acessar o WhatsApp
    |
    */
    'proxy' => env('WHATSAPP_PROXY', null),
]; 