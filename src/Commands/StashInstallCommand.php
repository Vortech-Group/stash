<?php

declare(strict_types=1);

namespace Vortech\Stash\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Vortech\Stash\Providers\StashServiceProvider;

#[Signature('stash:install
    {--force : Overwrite the existing configuration file}
    {--database : Also publish the migration of the database driver}')]
#[Description('Publish the Stash configuration file')]
final class StashInstallCommand extends Command
{
    public function handle(): int
    {
        $this->publish('stash-config');

        if ($this->option('database')) {
            $this->publishMigration();
        }

        $this->components->info('Stash installed. Use stash()->put(\'key\', \'value\') to store your first value.');

        return self::SUCCESS;
    }

    private function publishMigration(): void
    {
        if (glob(database_path('migrations/*_create_stash_table.php'))) {
            $this->components->warn('The stash migration is already published.');

            return;
        }

        $this->publish('stash-migrations');

        $this->components->info('Run "php artisan migrate" to create the stash table.');
    }

    private function publish(string $tag): void
    {
        $this->call('vendor:publish', [
            '--provider' => StashServiceProvider::class,
            '--tag' => $tag,
            '--force' => $this->option('force'),
        ]);
    }
}
