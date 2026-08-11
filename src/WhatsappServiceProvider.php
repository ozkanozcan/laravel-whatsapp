<?php

namespace OzkanOzcan\LaravelWhatsapp;

use Illuminate\Support\ServiceProvider;

class WhatsappServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/whatsapp.php',
            'whatsapp'
        );

        $this->app->singleton(WhatsappBot::class, function ($app) {
            return new WhatsappBot($app['config']['whatsapp']);
        });

        $this->app->singleton(WhatsappChannel::class, function ($app) {
            return new WhatsappChannel($app->make(WhatsappBot::class));
        });

        // Allow resolving via alias
        $this->app->alias(WhatsappBot::class, 'whatsapp');
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/whatsapp.php' => config_path('whatsapp.php'),
        ], 'whatsapp-config');

        // Publish language files
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/whatsapp'),
        ], 'whatsapp-lang');

        // Load package translations (vendor override supported)
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'whatsapp'
        );

        // Register Artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([
                WhatsappTestCommand::class,
            ]);
        }
    }
}
