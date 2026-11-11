<?php

namespace App\Providers;

use App\Shared\Auth\AccessControl;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Modules\Modules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One context per request / queued job (reset between them).
        $this->app->scoped(ConferenceContext::class);
        $this->app->singleton(AccessControl::class);

        foreach (Modules::providers() as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }
}
