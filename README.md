# Laravel WhatsApp Integration

Integração robusta entre Laravel e API WhatsApp Node.js com suporte a recursos avançados como tokens de atualização, circuit breaker, rastreamento de correlação, transações atômicas e muito mais.

## Instalação

Instale o pacote via Composer:

```bash
composer require desterroshop/laravel-whatsapp
```

### Publicar configurações

```bash
php artisan vendor:publish --provider="DesterroShop\LaravelWhatsApp\LaravelWhatsAppServiceProvider" --tag="config"
```

## Configuração

Ajuste as configurações em `config/whatsapp.php` ou defina as variáveis de ambiente no arquivo `.env`:

```
WHATSAPP_API_URL=http://localhost:3000
WHATSAPP_API_KEY=sua-api-key
WHATSAPP_DEFAULT_SESSION=default
```

## Recursos principais

### Autenticação avançada

O pacote oferece um sistema de autenticação com tokens de atualização para manter a segurança e evitar expiração:

```php
use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;

// Autenticar e obter tokens
$tokens = WhatsApp::authenticate();
// array('access_token' => '...', 'refresh_token' => '...', 'expires_in' => 3600)

// Renovar tokens
$newTokens = WhatsApp::refreshToken($refreshToken);

// Definir token para requisições
WhatsApp::withToken($accessToken)->sendText('5548999998888', 'Olá!');
```

### Rastreamento entre sistemas

O pacote oferece ID de correlação para rastrear requisições entre sistemas:

```php
// Definir ID de correlação
$result = WhatsApp::withCorrelationId('seu-id-unico')
    ->sendText('5548999998888', 'Olá!');

// Obter logs pelo ID de correlação
$logs = WhatsApp::getLogsByCorrelationId('seu-id-unico');
```

### Transações atômicas

Garanta a atomicidade de operações complexas:

```php
// Iniciar transação
$transactionId = WhatsApp::beginTransaction();

try {
    // Enviar múltiplas mensagens na mesma transação
    WhatsApp::withTransaction($transactionId)
        ->sendText('5548999998888', 'Primeira mensagem');
        
    WhatsApp::withTransaction($transactionId)
        ->sendText('5548999998888', 'Segunda mensagem');
        
    // Confirmar transação
    WhatsApp::commitTransaction($transactionId);
} catch (\Exception $e) {
    // Reverter em caso de falha
    WhatsApp::rollbackTransaction($transactionId);
    throw $e;
}
```

### Utilizando o Circuit Breaker

Proteja seu sistema contra falhas em cascata:

```php
use DesterroShop\LaravelWhatsApp\Services\CircuitBreakerService;

// Injetar o serviço
public function __construct(CircuitBreakerService $circuitBreaker)
{
    $this->circuitBreaker = $circuitBreaker;
}

// Executar operação protegida
$result = $this->circuitBreaker->execute('whatsapp-api', function () {
    return WhatsApp::sendText('5548999998888', 'Mensagem protegida por circuit breaker');
}, function () {
    // Fallback em caso de falha
    return ['success' => false, 'message' => 'Serviço indisponível'];
});
```

### Envio de mensagens

O pacote suporta vários tipos de mensagens:

```php
// Mensagem de texto
WhatsApp::sendText('5548999998888', 'Olá, como vai?', [
    'priority' => 'high',
    'delay' => 0,
    'quoted_message_id' => '12345'
]);

// Mensagem com template
WhatsApp::sendTemplate('5548999998888', 'boas_vindas', [
    'name' => 'João',
    'company' => 'DesterroShop'
]);

// Mensagem com mídia
WhatsApp::sendMedia(
    '5548999998888',
    'https://exemplo.com/imagem.jpg',
    'image',
    'Veja esta imagem incrível!'
);

// Mensagem com lista
WhatsApp::sendList(
    '5548999998888',
    'Selecione uma opção',
    'Ver opções',
    [
        ['id' => '1', 'title' => 'Opção 1', 'description' => 'Descrição da opção 1'],
        ['id' => '2', 'title' => 'Opção 2', 'description' => 'Descrição da opção 2'],
    ],
    'Por favor, escolha uma das opções abaixo'
);

// Mensagem com botões
WhatsApp::sendButton(
    '5548999998888',
    'Clique em um botão',
    [
        ['id' => 'btn1', 'text' => 'Sim'],
        ['id' => 'btn2', 'text' => 'Não'],
    ]
);
```

### Webhooks

Configure webhooks para receber eventos do WhatsApp:

```php
// Registrar webhook
WhatsApp::registerWebhook(
    'https://seu-site.com/api/whatsapp/webhook',
    ['message', 'message_ack', 'group_join']
);

// Listar webhooks
$webhooks = WhatsApp::listWebhooks();

// Remover webhook
WhatsApp::removeWebhook('https://seu-site.com/api/whatsapp/webhook');
```

### Manipular eventos de webhook

```php
// Em app/Http/Controllers/WhatsAppWebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? 'unknown';
        
        Log::info("Webhook WhatsApp recebido: {$event}", [
            'correlation_id' => $request->header('X-Correlation-ID'),
            'payload' => $payload
        ]);
        
        // Processar o evento de acordo com o tipo
        switch ($event) {
            case 'message':
                // Processar mensagem recebida
                break;
                
            case 'message_ack':
                // Processar confirmação de entrega
                break;
                
            // outros eventos...
        }
        
        return response()->json(['success' => true]);
    }
}
```

### Filas e Jobs

Para processamento assíncrono:

```php
use DesterroShop\LaravelWhatsApp\Jobs\ProcessWhatsAppJob;

// Agendar envio de mensagem
ProcessWhatsAppJob::dispatch('send-text', [
    'to' => '5548999998888',
    'message' => 'Mensagem agendada',
    'options' => [
        'priority' => 'medium',
    ]
], [
    'attempts' => 3,
    'delay' => 60, // segundos
    'correlation_id' => 'job-123',
    'priority' => 'high'
]);
```

## Middleware

### Correlation ID Middleware

Adicione o middleware no seu `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ...
        \DesterroShop\LaravelWhatsApp\Middleware\CorrelationIdMiddleware::class,
    ],
];
```

Este middleware adiciona automaticamente um ID de correlação para todas as requisições.

## Comandos Artisan

```bash
# Configurar webhook
php artisan whatsapp:setup-webhook https://seu-site.com/api/whatsapp/webhook

# Limpar transações expiradas
php artisan whatsapp:cleanup-transactions --older-than=60
```

## Tratamento de Erros

```php
use DesterroShop\LaravelWhatsApp\Exceptions\WhatsAppException;

try {
    WhatsApp::sendText('5548999998888', 'Olá!');
} catch (WhatsAppException $e) {
    // Tratar erro específico da API WhatsApp
    report($e);
    return response()->json(['error' => $e->getMessage()], $e->getCode());
} catch (\Exception $e) {
    // Tratar outros erros
    report($e);
    return response()->json(['error' => 'Erro interno do servidor'], 500);
}
```

## Integração com Laravel Sanctum

Adicione suporte ao Laravel Sanctum:

```php
// config/whatsapp.php
'sanctum' => [
    'enabled' => true,
    'proxy_url' => '/api/whatsapp/proxy',
],
```

Verifica tokens do Sanctum:

```php
// Verificar token Sanctum e autenticar com API
$result = WhatsApp::verifySanctumToken($sanctumToken);
```

## Testes

```bash
composer test
```

## Licença

Este pacote é open-source e licenciado sob a [Licença MIT](LICENSE.md). 