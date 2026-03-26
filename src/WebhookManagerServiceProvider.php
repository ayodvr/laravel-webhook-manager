<?php

namespace Cybrox\WebhookManager;

use Cybrox\WebhookManager\Http\Controllers\WebhookController;
use Cybrox\WebhookManager\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WebhookManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-manager.php', 'webhook-manager');
    }

    public function boot(): void
    {
        Route::aliasMiddleware('webhook.signature', VerifyWebhookSignature::class);
        $this->registerRoutes();
        $this->publishResources();

        // Register demo listener if in local/testing
        if ($this->app->environment('local', 'testing') && class_exists('App\Listeners\HandlePaystackDemo')) {
            \Illuminate\Support\Facades\Event::listen(
                \Cybrox\WebhookManager\Events\WebhookReceived::class,
                'App\Listeners\HandlePaystackDemo'
            );
        }
    }

    protected function registerRoutes(): void
    {
        $this->app['router']->middleware($this->middlewareAliases());

        $prefix = config('webhook-manager.route_prefix', 'webhooks');

        Route::prefix($prefix)
             ->middleware('webhook.signature')
             ->group(function () {
                 Route::post('/{provider}', [WebhookController::class, 'handle'])
                      ->name('webhook.handle');
             });
    }

    protected function middlewareAliases(): array
    {
        return [
            'webhook.signature' => VerifyWebhookSignature::class,
        ];
    }

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
