<?php

declare(strict_types=1);

namespace Vortech\Stash\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Vortech\Stash\Commands\StashInstallCommand;
use Vortech\Stash\StashManager;

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

        $this->app->singleton('stash', fn ($app) => new StashManager($app));
        $this->app->alias('stash', StashManager::class);
    }

    private function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            paths: [
                __DIR__ . '/../../config/stash.php' => config_path('stash.php'),
            ],
            groups: 'stash-config'
        );

        $this->publishes(
            paths: [
                __DIR__ . '/../../database/migrations/create_stash_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His') . '_create_stash_table.php'
                ),
            ],
            groups: 'stash-migrations'
        );

        $this->commands([
            StashInstallCommand::class,
        ]);
    }

    private function configureBladeDirectives(): void
    {
        Blade::directive('stash', function ($value) {
            return "<?php echo app('stash')->get($value); ?>";
        });
    }
}
