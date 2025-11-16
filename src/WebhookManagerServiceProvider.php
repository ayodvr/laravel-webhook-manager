<?php

namespace Cybrox\WebhookManager;

use Cybrox\WebhookManager\Http\Controllers\WebhookController;
use Cybrox\WebhookManager\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for WebhookManager package
 */
class WebhookManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-manager.php', 'webhook-manager');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('webhook.signature', VerifyWebhookSignature::class);
        $this->registerRoutes();
        $this->publishResources();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        $this->app['router']->middleware($this->middlewareAliases());

        Route::middleware('webhook.signature')
             ->group(function () {
                 Route::post('/webhooks/{provider}', [WebhookController::class, 'handle'])
                      ->name('webhook.handle');
             });
    }

    /**
     * Middleware aliases.
     */
    protected function middlewareAliases(): array
    {
        return [
            'webhook.signature' => VerifyWebhookSignature::class,
        ];
    }

    /**
     * Publish resources.
     */
    protected function publishResources(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'webhook-migrations');

        $this->publishes([
            __DIR__.'/../config/webhook-manager.php' => config_path('webhook-manager.php'),
        ], 'webhook-config');
    }
}
