<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações da API WhatsApp
    |--------------------------------------------------------------------------
    |
    | Configurações relacionadas à conexão com a API do WhatsApp
    |
    */
    'api_url' => env('WHATSAPP_API_URL', 'http://localhost:3000'),
    'api_token' => env('WHATSAPP_API_TOKEN', ''),
    'api_key' => env('WHATSAPP_API_KEY', ''),
    'default_session' => env('WHATSAPP_DEFAULT_SESSION', 'default'),
    'request_timeout' => env('WHATSAPP_REQUEST_TIMEOUT', 30),
    'qr_timeout' => env('WHATSAPP_QR_TIMEOUT', 60),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Autenticação
    |--------------------------------------------------------------------------
    |
    | Configurações para autenticação JWT e Refresh Token
    |
    */
    'auth' => [
        'jwt_secret' => env('WHATSAPP_JWT_SECRET', ''),
        'jwt_expiration' => env('WHATSAPP_JWT_EXPIRATION', '1d'),
        'refresh_secret' => env('WHATSAPP_JWT_REFRESH_SECRET', ''),
        'refresh_expiration' => env('WHATSAPP_JWT_REFRESH_EXPIRATION', '7d'),
        'store_tokens' => env('WHATSAPP_STORE_TOKENS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Circuit Breaker
    |--------------------------------------------------------------------------
    |
    | Configurações para o padrão Circuit Breaker
    |
    */
    'circuit_breaker' => [
        'threshold' => env('WHATSAPP_CIRCUIT_BREAKER_THRESHOLD', 5),
        'timeout' => env('WHATSAPP_CIRCUIT_BREAKER_TIMEOUT', 30000),
        'reset_timeout' => env('WHATSAPP_CIRCUIT_BREAKER_RESET_TIMEOUT', 60000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Transações
    |--------------------------------------------------------------------------
    |
    | Configurações para transações atômicas
    |
    */
    'transaction' => [
        'timeout' => env('WHATSAPP_TRANSACTION_TIMEOUT', 30000),
        'auto_rollback' => env('WHATSAPP_TRANSACTION_AUTO_ROLLBACK', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Webhook
    |--------------------------------------------------------------------------
    |
    | Configurações para o webhook que recebe eventos do WhatsApp
    |
    */
    'webhook' => [
        'url' => env('WHATSAPP_WEBHOOK_URL', '/webhook/whatsapp'),
        'secret' => env('WHATSAPP_WEBHOOK_SECRET', null),
        'events' => [
            'message', 
            'message_ack', 
            'disconnected', 
            'ready'
        ],
        'middleware' => [],
        'max_retries' => env('WHATSAPP_WEBHOOK_MAX_RETRIES', 3),
        'retry_delay' => env('WHATSAPP_WEBHOOK_RETRY_DELAY', 1000),
        'backoff_factor' => env('WHATSAPP_WEBHOOK_BACKOFF_FACTOR', 1.5),
        'jitter' => env('WHATSAPP_WEBHOOK_JITTER', 0.2),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Queue
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com sistema de filas
    |
    */
    'queue' => [
        'connection' => env('WHATSAPP_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'redis')),
        'queue' => env('WHATSAPP_QUEUE', 'whatsapp'),
        'default_priority' => env('WHATSAPP_QUEUE_DEFAULT_PRIORITY', 'medium'),
        'delayed_debounce' => env('WHATSAPP_QUEUE_DELAYED_DEBOUNCE', 300),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Media
    |--------------------------------------------------------------------------
    |
    | Configurações para armazenamento de mídia
    |
    */
    'media' => [
        'storage' => env('WHATSAPP_MEDIA_STORAGE', 'local'),
        'path' => env('WHATSAPP_MEDIA_PATH', 'whatsapp'),
        'disk' => env('WHATSAPP_MEDIA_DISK', 'public'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Templates
    |--------------------------------------------------------------------------
    |
    | Configurações para templates de mensagens
    |
    */
    'templates' => [
        'path' => env('WHATSAPP_TEMPLATES_PATH', resource_path('views/vendor/whatsapp/templates')),
        'cache' => env('WHATSAPP_CACHE_TEMPLATES', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Broadcast
    |--------------------------------------------------------------------------
    |
    | Configurações para transmissão de eventos
    |
    */
    'broadcast' => [
        'enabled' => env('WHATSAPP_BROADCAST_EVENTS', true),
        'channel' => env('WHATSAPP_BROADCAST_CHANNEL', 'whatsapp'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Logs
    |--------------------------------------------------------------------------
    |
    | Configurações para logging
    |
    */
    'log' => [
        'channel' => env('WHATSAPP_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
        'level' => env('WHATSAPP_LOG_LEVEL', 'debug'),
        'correlation_id' => env('WHATSAPP_LOG_CORRELATION_ID', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Integração com Laravel Sanctum
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com Laravel Sanctum
    |
    */
    'sanctum' => [
        'enabled' => env('WHATSAPP_SANCTUM_ENABLED', false),
        'proxy_url' => env('WHATSAPP_SANCTUM_PROXY_URL', '/api/whatsapp/proxy'),
    ],
    
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
        'api' => ['api'],
    ],
]; 