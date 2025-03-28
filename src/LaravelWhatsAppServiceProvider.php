<?php

namespace LucasGiovanni\LaravelWhatsApp;

use LucasGiovanni\LaravelWhatsApp\Console\Commands\WhatsAppQrCodeCommand;
use LucasGiovanni\LaravelWhatsApp\Console\Commands\WhatsAppSendMessageCommand;
use LucasGiovanni\LaravelWhatsApp\Console\Commands\WhatsAppSessionsCommand;
use LucasGiovanni\LaravelWhatsApp\Middleware\CorrelationIdMiddleware;
use LucasGiovanni\LaravelWhatsApp\Services\CircuitBreakerService;
use LucasGiovanni\LaravelWhatsApp\Services\RefreshTokenService;
use LucasGiovanni\LaravelWhatsApp\Services\TransactionService;
use LucasGiovanni\LaravelWhatsApp\Services\WhatsAppService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelWhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publicar configuração
        $this->publishes([
            __DIR__.'/../config/whatsapp.php' => config_path('whatsapp.php'),
        ], 'config');

        // Publicar migrações
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Publicar assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/whatsapp'),
        ], 'assets');

        // Publicar views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/whatsapp'),
        ], 'views');

        // Carregar rotas
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Carregar visualizações
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'whatsapp');

        // Carregar traduções
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'whatsapp');

        // Registrar middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('correlation.id', CorrelationIdMiddleware::class);

        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                WhatsAppQrCodeCommand::class,
                WhatsAppSendMessageCommand::class,
                WhatsAppSessionsCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Mesclar configurações
        $this->mergeConfigFrom(
            __DIR__.'/../config/whatsapp.php', 'whatsapp'
        );

        // Registro do serviço principal WhatsApp
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService(
                config('whatsapp.api_url'),
                config('whatsapp.api_key'),
                config('whatsapp.timeout', 30)
            );
        });

        // Registro de serviços adicionais
        $this->app->singleton(CircuitBreakerService::class, function ($app) {
            return new CircuitBreakerService(
                $app->make(WhatsAppService::class)
            );
        });

        $this->app->singleton(TransactionService::class, function ($app) {
            return new TransactionService(
                $app->make(WhatsAppService::class)
            );
        });

        $this->app->singleton(RefreshTokenService::class, function ($app) {
            return new RefreshTokenService(
                $app->make(WhatsAppService::class)
            );
        });

        // Registrar alias para facilitar o uso
        $this->app->alias(WhatsAppService::class, 'whatsapp');
        $this->app->alias(CircuitBreakerService::class, 'whatsapp.circuit');
        $this->app->alias(TransactionService::class, 'whatsapp.transaction');
        $this->app->alias(RefreshTokenService::class, 'whatsapp.token');
    }

    /**
     * Obter os serviços fornecidos pelo provedor.
     */
    public function provides(): array
    {
        return [
            WhatsAppService::class,
            CircuitBreakerService::class,
            TransactionService::class,
            RefreshTokenService::class,
            'whatsapp',
            'whatsapp.circuit',
            'whatsapp.transaction',
            'whatsapp.token',
        ];
    }
} 