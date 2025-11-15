<?php

namespace VisioSoft\Support;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use VisioSoft\Support\Models\PartnerSupport;
use VisioSoft\Support\Models\PartnerSupportReply;
use VisioSoft\Support\Policies\PartnerSupportPolicy;
use VisioSoft\Support\Policies\PartnerSupportReplyPolicy;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/support.php',
            'support'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'support');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/support.php' => $this->app->configPath('support.php'),
            ], 'support-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => $this->app->databasePath('migrations'),
            ], 'support-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/support'),
            ], 'support-views');
        }

        Gate::policy(PartnerSupport::class, PartnerSupportPolicy::class);
        Gate::policy(PartnerSupportReply::class, PartnerSupportReplyPolicy::class);
    }
}
