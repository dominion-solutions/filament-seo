<?php

namespace DominionSolutions\FilamentSeo;

use Illuminate\Support\ServiceProvider;

class FilamentSeoServiceProvider extends ServiceProvider
{
    /**
     * The package ships its migrations in place so `php artisan migrate` just
     * works. Publishing is available for teams that prefer migrations to live
     * in the application.
     */
    protected function publishables(): array
    {
        return [
            __DIR__.'/../config/filament-seo.php' => config_path('filament-seo.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ];
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-seo.php', 'filament-seo');

        $this->app->scoped(SeoRegistry::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes($this->publishables(), 'filament-seo');
        }
    }
}
