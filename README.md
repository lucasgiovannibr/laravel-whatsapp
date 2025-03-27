# Pacote de Integração Laravel WhatsApp

[![Latest Version on Packagist](https://img.shields.io/packagist/v/desterroshop/laravel-whatsapp.svg?style=flat-square)](https://packagist.org/packages/desterroshop/laravel-whatsapp)
[![Total Downloads](https://img.shields.io/packagist/dt/desterroshop/laravel-whatsapp.svg?style=flat-square)](https://packagist.org/packages/desterroshop/laravel-whatsapp)
[![PHP Version](https://img.shields.io/packagist/php-v/desterroshop/laravel-whatsapp.svg?style=flat-square)](https://packagist.org/packages/desterroshop/laravel-whatsapp)
[![License](https://img.shields.io/github/license/desterroshop/laravel-whatsapp?style=flat-square)](https://github.com/desterroshop/laravel-whatsapp/blob/master/LICENSE)

Pacote oficial para integração do Laravel com a API DesterroShop WhatsApp.

- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Uso](#uso)
  - [Envio de Mensagens](#envio-de-mensagens)
  - [Recebimento de Mensagens](#recebimento-de-mensagens)
  - [Sistema de Notificações](#sistema-de-notificações)
  - [Templates de Mensagem](#templates-de-mensagem)
- [Documentação Completa](#documentação-completa)
- [Licença](#licença)
- [Suporte](#suporte)

## Requisitos

- PHP 8.1 ou superior
- Laravel 10.x ou 11.x
- Servidor WhatsApp API em execução
- Extensão ext-json do PHP

## Instalação

Instale o pacote via Composer:

```bash
composer require lucasgiovanni/laravel-whatsapp
```

O pacote utiliza auto-discovery do Laravel, então o Service Provider será registrado automaticamente.

Em seguida, publique os arquivos de configuração e migrações:

```bash
php artisan vendor:publish --provider="DesterroShop\LaravelWhatsApp\WhatsAppServiceProvider" --tag="whatsapp-config"
php artisan vendor:publish --provider="DesterroShop\LaravelWhatsApp\WhatsAppServiceProvider" --tag="whatsapp-migrations"
```

Execute as migrações para criar as tabelas necessárias:

```bash
php artisan migrate
```

## Configuração

Adicione as seguintes variáveis de ambiente ao seu arquivo `.env`:

```
WHATSAPP_API_URL=http://localhost:8787
WHATSAPP_API_TOKEN=seu_token_jwt
WHATSAPP_DEFAULT_SESSION=default
WHATSAPP_WEBHOOK_SECRET=seu_webhook_secret
```

Para utilizar com múltiplas sessões, você pode criar uma configuração específica para cada sessão no arquivo `config/whatsapp.php`.

## Uso

### Envio de Mensagens

Você pode utilizar a Facade `WhatsApp` para enviar mensagens:

```php
use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;

// Enviar texto simples
WhatsApp::sendText('5548999998888', 'Olá, esta é uma mensagem de teste!');

// Enviar imagem
WhatsApp::sendImage('5548999998888', 'https://exemplo.com/imagem.jpg', 'Legenda opcional');

// Enviar documento
WhatsApp::sendFile('5548999998888', 'https://exemplo.com/documento.pdf', 'Relatório 2023');

// Enviar localização
WhatsApp::sendLocation('5548999998888', -27.5969, -48.5495, 'Florianópolis/SC');

// Enviar mensagem com template
WhatsApp::sendTemplate('5548999998888', 'boas-vindas', [
    'nome' => 'João Silva',
    'empresa' => 'DesterroShop'
]);
```

### Recebimento de Mensagens

Configure o webhook no seu arquivo `.env`:

```
WHATSAPP_WEBHOOK_URL=https://sua-aplicacao.com/webhook/whatsapp
```

Na sua aplicação Laravel, você pode criar rotas que respondem a mensagens específicas:

```php
// routes/web.php
use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;

// Respondem a mensagens específicas
Route::whatsapp('oi,olá,início', function() {
    $from = request()->input('message.from');
    WhatsApp::sendText($from, 'Olá! Como posso ajudar?');
});

// Responde a qualquer mensagem
Route::post('webhook/whatsapp', 'WhatsAppWebhookController@handle')
    ->middleware('whatsapp.webhook');
```

O middleware `whatsapp.webhook` irá verificar a assinatura do webhook para garantir que a requisição é válida.

### Sistema de Notificações

O pacote inclui um canal de notificação para WhatsApp. Você pode utilizá-lo da seguinte forma:

```php
// app/Notifications/PedidoConfirmado.php

use DesterroShop\LaravelWhatsApp\Notifications\Channels\WhatsAppChannel;
use Illuminate\Notifications\Notification;

class PedidoConfirmado extends Notification
{
    public function via($notifiable)
    {
        return ['mail', WhatsAppChannel::class];
    }
    
    public function toWhatsApp($notifiable)
    {
        return [
            'message' => "Olá {$notifiable->name}, seu pedido #{$this->pedido->codigo} foi confirmado!",
            // ou usando template:
            'template' => 'pedido-confirmado',
            'data' => [
                'numero' => $this->pedido->codigo,
                'valor' => $this->pedido->valor,
                'prazo' => $this->pedido->prazo
            ]
        ];
    }
}
```

Para que um modelo possa receber notificações via WhatsApp, ele deve implementar a interface `DesterroShop\LaravelWhatsApp\Contracts\WhatsAppNotifiable`:

```php
// app/Models/User.php

use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppNotifiable;
use DesterroShop\LaravelWhatsApp\Traits\ReceivesWhatsApp;

class User extends Authenticatable implements WhatsAppNotifiable
{
    use ReceivesWhatsApp;
    
    // Método opcional caso o número de telefone não esteja no campo 'phone'
    public function routeNotificationForWhatsApp()
    {
        return $this->whatsapp_number;
    }
}
```

### Templates de Mensagem

Você pode criar templates de mensagem usando Handlebars:

```php
// Criar ou atualizar um template
app(DesterroShop\LaravelWhatsApp\Services\TemplateService::class)->saveTemplate(
    'pedido-confirmado',
    'Olá {{nome}}, seu pedido #{{numero}} no valor de {{valor}} foi confirmado!'
);

// Enviar mensagem usando o template
WhatsApp::sendTemplate('5548999998888', 'pedido-confirmado', [
    'nome' => 'Maria',
    'numero' => '12345',
    'valor' => 'R$ 150,00'
]);
```

Você também pode usar diretivas Blade para templates:

```php
// Em uma view Blade
@whatsappTemplate('pedido-confirmado', ['nome' => 'Pedro'])
```

## Suporte a Múltiplas Sessões

Para trabalhar com múltiplas sessões de WhatsApp:

```php
// Listar todas as sessões
$sessions = WhatsApp::sessions();

// Usar uma sessão específica
WhatsApp::session('vendas')->sendText('5548999998888', 'Mensagem da equipe de vendas');
WhatsApp::session('suporte')->sendText('5548999997777', 'Mensagem da equipe de suporte');
```

## Documentação Completa

Para a documentação completa, visite [a wiki do projeto](https://github.com/desterroshop/laravel-whatsapp/wiki).

## Licença

Este pacote é licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## Suporte

Para suporte, entre em contato pelo email [suporte@desterroshop.com](mailto:suporte@desterroshop.com).

---

Feito com ❤️ pela DesterroShop 