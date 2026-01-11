<?php

declare(strict_types=1);

namespace Vortech\Stash\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vortech\Stash\Stash;

final class StashServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->offerPublishing();

        $this->configureBladeDirectives();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/stash.php',
            key: 'stash'
        );

        $this->app->singleton('stash', function () {
            return new Stash;
        });
    }

    private function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [
                __DIR__ . '/../../config/stash.php' => config_path('stash.php')
            ],
            groups: 'stash-config'
        );
    }

    private function configureBladeDirectives(): void
    {
        Blade::directive('stash', function ($value) {
            return "<?php echo app('stash')->get($value); ?>";
        });
    }
}
