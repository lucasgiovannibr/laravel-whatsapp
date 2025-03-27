# Próximos Passos para Integração WhatsApp com Laravel

Este documento contém instruções sobre como implementar e configurar a integração entre o Laravel e a API DesterroShop WhatsApp.

## 1. Instalação do Pacote

Execute o comando abaixo para instalar o pacote via Composer:

```bash
composer require lucasgiovanni/laravel-whatsapp
```

## 2. Publicação dos Arquivos de Configuração

Publique os arquivos de configuração e assets:

```bash
php artisan vendor:publish --provider="DesterroShop\LaravelWhatsApp\WhatsAppServiceProvider"
```

## 3. Configuração das Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```
WHATSAPP_API_URL=https://seu-servidor-whatsapp.com
WHATSAPP_API_KEY=sua-chave-api-aqui
WHATSAPP_DEFAULT_SESSION=default
WHATSAPP_WEBHOOK_SECRET=segredo-para-validar-webhooks
```

## 4. Configuração do Webhook

Configure o webhook para receber mensagens do WhatsApp:

1. Crie uma rota em `routes/api.php`:

```php
Route::post('/webhook/whatsapp', function () {
    return response()->json(['status' => 'success']);
})->middleware(\DesterroShop\LaravelWhatsApp\Http\Middleware\WhatsAppWebhookMiddleware::class);
```

2. Configure a URL do webhook no seu servidor WhatsApp:

```php
// Em AppServiceProvider ou outro local apropriado
\DesterroShop\LaravelWhatsApp\Facades\WhatsApp::setWebhook(
    route('api.whatsapp.webhook')
);
```

## 5. Implementação de Exemplos Básicos

### Enviando Mensagens Simples

```php
use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;

// Enviar texto
WhatsApp::sendText('5548999998888', 'Olá, tudo bem?');

// Enviar imagem
WhatsApp::sendImage('5548999998888', 'https://exemplo.com/imagem.jpg', 'Legenda da imagem');

// Enviar arquivo
WhatsApp::sendFile('5548999998888', storage_path('app/documentos/contrato.pdf'), 'Contrato.pdf');
```

### Usando Templates

```php
// Criar um template
WhatsApp::createTemplate('boas-vindas', 'Olá {{nome}}, bem-vindo à {{empresa}}!');

// Enviar usando o template
WhatsApp::sendTemplate('5548999998888', 'boas-vindas', [
    'nome' => 'João',
    'empresa' => 'DesterroShop'
]);
```

## 6. Implementação de Recursos Avançados

### Novos Tipos de Mensagens

O pacote agora suporta diversos tipos avançados de mensagens interativas:

#### Enviando Listas

Perfeito para mostrar menus, catálogos ou opções aos usuários:

```php
WhatsApp::sendList(
    '5548999998888',
    'Nosso Menu',
    'Escolha uma categoria',
    'Ver Opções',
    [
        [
            'title' => 'Pratos Principais',
            'rows' => [
                ['id' => 'lasanha', 'title' => 'Lasanha', 'description' => 'R$ 35,90'],
                ['id' => 'pizza', 'title' => 'Pizza', 'description' => 'A partir de R$ 42,90']
            ]
        ],
        [
            'title' => 'Bebidas',
            'rows' => [
                ['id' => 'refrigerante', 'title' => 'Refrigerante', 'description' => 'R$ 8,90'],
                ['id' => 'suco', 'title' => 'Sucos Naturais', 'description' => 'R$ 12,90']
            ]
        ]
    ]
);
```

#### Enviando Enquetes

Ideal para coletar feedback ou votações:

```php
WhatsApp::sendPoll(
    '5548999998888',
    'Como foi sua experiência com nosso produto?',
    ['Excelente', 'Boa', 'Regular', 'Ruim', 'Péssima'],
    false // escolha única
);
```

#### Enviando Produtos do Catálogo

Para lojas virtuais que usam catálogo do WhatsApp Business:

```php
// Enviar produto específico
WhatsApp::sendProduct('5548999998888', 'catalog_id_123', 'product_id_456');

// Enviar múltiplos produtos em um catálogo
WhatsApp::sendCatalog('5548999998888', 'catalog_id_123', ['product_id_123', 'product_id_456']);
```

#### Enviando Pedidos

Para lojas que precisam formalizar pedidos via WhatsApp:

```php
WhatsApp::sendOrder(
    '5548999998888',
    [
        'order_id' => '12345',
        'catalog_id' => 'catalog_id_123',
        'items' => [
            [
                'product_id' => 'product_id_456',
                'quantity' => 2,
                'price' => 99.90
            ]
        ],
        'total' => 199.80,
        'currency' => 'BRL'
    ]
);
```

### Processando Respostas

Todos esses tipos de mensagens geram respostas específicas que podem ser processadas:

```php
// Em seu EventServiceProvider
Event::listen(WhatsAppMessageReceived::class, function (WhatsAppMessageReceived $event) {
    $message = $event->message;
    
    if ($message['type'] === 'list_response') {
        // Processar resposta de lista
        $selectedOption = $message['list_response']['id'];
        
        // Fazer algo com a seleção
    }
    
    if ($message['type'] === 'poll_response') {
        // Processar resposta de enquete
        $selectedOptions = $message['poll_response']['options'];
        
        // Fazer algo com as opções selecionadas
    }
});
```

## 7. Testando a Integração

Para verificar se sua integração está funcionando corretamente:

1. Execute o comando para verificar o status da sessão:

```bash
php artisan whatsapp:sessions status default
```

2. Envie uma mensagem de teste:

```bash
php artisan whatsapp:send 5548999998888 "Esta é uma mensagem de teste"
```

3. Verifique os logs para qualquer erro:

```bash
tail -f storage/logs/laravel.log
```

## 8. Documentação Adicional

Para mais detalhes sobre os tipos de mensagens suportados e como utilizá-los, consulte o arquivo:

- [Tipos de Mensagens Suportados](docs/TIPOS_MENSAGENS.md)

## 9. Próximos Passos

- Configurar filas para processamento assíncrono de mensagens
- Implementar armazenamento de mídia para imagens e arquivos
- Configurar monitoramento de status de entrega
- Desenvolver interface administrativa para gerenciar templates
- Integrar com sistema de notificações existente

## 10. Suporte

Se encontrar problemas durante a implementação, consulte:

- Documentação completa no diretório `docs/`
- Exemplos práticos no diretório `examples/`
- Abra uma issue no repositório GitHub 