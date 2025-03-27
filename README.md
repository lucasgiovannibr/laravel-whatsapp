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

O pacote suporta diversos tipos de mensagens para atender a todas as necessidades:

#### Mensagem de texto

```php
// Mensagem de texto simples
WhatsApp::sendText(
    '5548999998888',           // Número do destinatário
    'Olá, como vai?',          // Texto da mensagem
    [                          // Opções adicionais (opcional)
        'priority' => 'high',  // Prioridade: high, medium, low
        'delay' => 0,          // Atraso em segundos
        'quoted_message_id' => '12345' // ID da mensagem que está sendo respondida
    ],
    'default'                  // ID da sessão (opcional)
);
```

#### Mensagem com template

```php
// Mensagem com template
WhatsApp::sendTemplate(
    '5548999998888',         // Número do destinatário
    'boas_vindas',           // Nome do template
    [                        // Dados para o template
        'name' => 'João',
        'company' => 'DesterroShop'
    ],
    'default'                // ID da sessão (opcional)
);
```

#### Mensagem com imagem

```php
// Enviar uma imagem
WhatsApp::sendImage(
    '5548999998888',                   // Número do destinatário
    'https://exemplo.com/imagem.jpg',  // URL da imagem
    'Veja esta imagem incrível!',      // Legenda (opcional)
    'default'                          // ID da sessão (opcional)
);
```

#### Mensagem com arquivo/documento

```php
// Enviar um arquivo/documento
WhatsApp::sendFile(
    '5548999998888',                   // Número do destinatário
    'https://exemplo.com/contrato.pdf', // URL do arquivo
    'Contrato.pdf',                    // Nome do arquivo (opcional)
    'default'                          // ID da sessão (opcional)
);
```

#### Mensagem com áudio

```php
// Enviar um áudio
WhatsApp::sendAudio(
    '5548999998888',                   // Número do destinatário
    'https://exemplo.com/audio.mp3',   // URL do áudio
    'default'                          // ID da sessão (opcional)
);
```

#### Mensagem com mídia genérica

```php
// Enviar mídia genérica (imagem, vídeo, documento, áudio)
WhatsApp::sendMedia(
    '5548999998888',                   // Número do destinatário
    'https://exemplo.com/video.mp4',   // URL da mídia
    'video',                           // Tipo: image, video, document, audio
    'Veja este vídeo!',                // Legenda (opcional)
    'default'                          // ID da sessão (opcional)
);
```

#### Mensagem com localização

```php
// Enviar localização
WhatsApp::sendLocation(
    '5548999998888',  // Número do destinatário
    -27.5969,         // Latitude
    -48.5495,         // Longitude
    'DesterroShop',   // Título (opcional)
    'default'         // ID da sessão (opcional)
);
```

#### Mensagem com contato

```php
// Enviar contato
WhatsApp::sendContact(
    '5548999998888',  // Número do destinatário
    [                 // Dados do contato
        'name' => [
            'first_name' => 'João',
            'last_name' => 'Silva',
            'formatted_name' => 'João Silva'
        ],
        'phones' => [
            [
                'phone' => '+5548999997777',
                'type' => 'CELL'
            ]
        ],
        'emails' => [
            [
                'email' => 'joao@example.com',
                'type' => 'WORK'
            ]
        ]
    ],
    'default'         // ID da sessão (opcional)
);
```

#### Mensagem com botões

```php
// Enviar mensagem com botões
WhatsApp::sendButtons(
    '5548999998888',  // Número do destinatário
    'Escolha uma opção:', // Texto principal
    [                 // Lista de botões
        [
            'id' => 'btn1',
            'text' => 'Sim'
        ],
        [
            'id' => 'btn2',
            'text' => 'Não'
        ],
        [
            'id' => 'btn3',
            'text' => 'Talvez'
        ]
    ],
    'default'         // ID da sessão (opcional)
);
```

#### Mensagem com lista de opções

```php
// Enviar lista de opções
WhatsApp::sendList(
    '5548999998888',     // Número do destinatário
    'Cardápio do dia',   // Título
    'Escolha seu prato', // Descrição
    'Ver opções',        // Texto do botão
    [                    // Seções da lista
        [
            'title' => 'Pratos principais',
            'rows' => [
                ['id' => 'p1', 'title' => 'Feijoada', 'description' => 'Porção completa'],
                ['id' => 'p2', 'title' => 'Lasanha', 'description' => 'De carne']
            ]
        ],
        [
            'title' => 'Sobremesas',
            'rows' => [
                ['id' => 's1', 'title' => 'Pudim', 'description' => 'Tradicional'],
                ['id' => 's2', 'title' => 'Sorvete', 'description' => 'De chocolate']
            ]
        ]
    ],
    'default'            // ID da sessão (opcional)
);
```

#### Mensagem com enquete

```php
// Enviar enquete
WhatsApp::sendPoll(
    '5548999998888',             // Número do destinatário
    'Qual sua cor favorita?',    // Pergunta
    [                           // Opções
        'Azul',
        'Verde',
        'Vermelho',
        'Amarelo'
    ],
    false,                      // Permitir múltipla escolha
    'default'                   // ID da sessão (opcional)
);
```

#### Mensagem com produto

```php
// Enviar produto do catálogo
WhatsApp::sendProduct(
    '5548999998888',  // Número do destinatário
    'cat123',         // ID do catálogo
    'prod456',        // ID do produto
    'default'         // ID da sessão (opcional)
);
```

#### Mensagem com catálogo

```php
// Enviar catálogo de produtos
WhatsApp::sendCatalog(
    '5548999998888',  // Número do destinatário
    'cat123',         // ID do catálogo
    [                 // Lista de produtos (opcional)
        'prod456',
        'prod789'
    ],
    'default'         // ID da sessão (opcional)
);
```

#### Mensagem com pedido

```php
// Enviar pedido
WhatsApp::sendOrder(
    '5548999998888',  // Número do destinatário
    [                 // Dados do pedido
        'catalog_id' => 'cat123',
        'items' => [
            [
                'product_id' => 'prod456',
                'quantity' => 2,
                'price' => 1990
            ],
            [
                'product_id' => 'prod789',
                'quantity' => 1,
                'price' => 2490
            ]
        ],
        'customer_details' => [
            'name' => 'João Silva',
            'address' => 'Rua Exemplo, 123'
        ]
    ],
    'default'         // ID da sessão (opcional)
);
```

### Agendamento de mensagens

```php
// Agendar uma mensagem
WhatsApp::scheduleMessage([
    'type' => 'text',
    'to' => '5548999998888',
    'message' => 'Lembrete: Reunião às 15h',
    'schedule_time' => '2023-12-31T15:00:00Z',
    'options' => [
        'priority' => 'medium'
    ]
]);

// Cancelar uma mensagem agendada
WhatsApp::cancelScheduledMessage('message_id_123');
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