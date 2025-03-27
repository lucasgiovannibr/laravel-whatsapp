<?php

namespace DesterroShop\LaravelWhatsApp;

use DesterroShop\LaravelWhatsApp\Console\Commands\WhatsAppSessionsCommand;
use DesterroShop\LaravelWhatsApp\Console\Commands\WhatsAppSendMessageCommand;
use DesterroShop\LaravelWhatsApp\Console\Commands\WhatsAppQrCodeCommand;
use DesterroShop\LaravelWhatsApp\Contracts\WhatsAppClient;
use DesterroShop\LaravelWhatsApp\Facades\WhatsApp;
use DesterroShop\LaravelWhatsApp\Http\Middleware\WhatsAppWebhookMiddleware;
use DesterroShop\LaravelWhatsApp\Services\WhatsAppService;
use DesterroShop\LaravelWhatsApp\Services\TemplateService;
use DesterroShop\LaravelWhatsApp\Notifications\Channels\WhatsAppChannel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar o arquivo de configuração
        $this->mergeConfigFrom(
            __DIR__.'/../config/whatsapp.php', 'whatsapp'
        );

        // Registrar serviços
        $this->app->singleton(WhatsAppClient::class, function ($app) {
            return new WhatsAppService(
                config('whatsapp.api_url'),
                config('whatsapp.api_token'),
                config('whatsapp.default_session')
            );
        });

        $this->app->singleton(WhatsApp::class, function ($app) {
            return $app->make(WhatsAppClient::class);
        });

        $this->app->singleton(TemplateService::class, function ($app) {
            return new TemplateService();
        });

        // Registrar o canal de notificação
        $this->app->singleton(WhatsAppChannel::class, function ($app) {
            return new WhatsAppChannel($app->make(WhatsAppClient::class));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publicar configurações
        $this->publishes([
            __DIR__.'/../config/whatsapp.php' => config_path('whatsapp.php'),
        ], 'whatsapp-config');

        // Publicar migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'whatsapp-migrations');

        // Publicar views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/whatsapp'),
        ], 'whatsapp-views');

        // Publicar assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/whatsapp'),
        ], 'whatsapp-assets');

        // Carregar rotas
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Carregar views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'whatsapp');

        // Carregar migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Registrar comandos Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                WhatsAppSessionsCommand::class,
                WhatsAppSendMessageCommand::class,
                WhatsAppQrCodeCommand::class,
            ]);
        }

        // Registrar macros
        $this->registerRouteMacros();
        $this->registerBladeMacros();
    }

    /**
     * Registrar macros de rota
     *
     * @return void
     */
    protected function registerRouteMacros()
    {
        Route::macro('whatsapp', function ($keywords, $action) {
            return Route::post('webhook/whatsapp', $action)
                ->middleware(WhatsAppWebhookMiddleware::class . ':' . $keywords);
        });
    }

    /**
     * Registrar macros Blade
     *
     * @return void
     */
    protected function registerBladeMacros()
    {
        Blade::directive('whatsappTemplate', function ($expression) {
            return "<?php echo app('" . TemplateService::class . "')->renderTemplate($expression); ?>";
        });
    }
} 