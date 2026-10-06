<?php

declare(strict_types=1);

beforeEach(function () {
    $this->configPath = config_path('stash.php');

    $this->cleanup = function () {
        @unlink($this->configPath);

        foreach (glob(database_path('migrations/*_create_stash_table.php')) ?: [] as $file) {
            unlink($file);
        }
    };

    ($this->cleanup)();
});

afterEach(fn () => ($this->cleanup)());

it('publishes the config file', function () {
    $this->artisan('stash:install')->assertSuccessful();

    expect($this->configPath)->toBeFile();
});

it('does not overwrite an existing config without --force', function () {
    file_put_contents($this->configPath, '<?php return [];');

    $this->artisan('stash:install')->assertSuccessful();

    expect(file_get_contents($this->configPath))->toBe('<?php return [];');
});

it('overwrites an existing config with --force', function () {
    file_put_contents($this->configPath, '<?php return [];');

    $this->artisan('stash:install', ['--force' => true])->assertSuccessful();

    expect(file_get_contents($this->configPath))->toContain('drivers');
});

it('only publishes the migration with --database', function () {
    $this->artisan('stash:install')->assertSuccessful();

    expect(glob(database_path('migrations/*_create_stash_table.php')))->toBeEmpty();

    $this->artisan('stash:install', ['--database' => true])->assertSuccessful();

    expect(glob(database_path('migrations/*_create_stash_table.php')))->toHaveCount(1);
});

it('does not publish the migration twice', function () {
    $this->artisan('stash:install', ['--database' => true])->assertSuccessful();
    $this->artisan('stash:install', ['--database' => true])->assertSuccessful();

    expect(glob(database_path('migrations/*_create_stash_table.php')))->toHaveCount(1);
});
