<?php

namespace DominionSolutions\FilamentSeo\Tests;

use DominionSolutions\FilamentSeo\FilamentSeoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base class for tests that need a booted Laravel application.
 *
 * The `Support` classes are covered by plain PHPUnit tests, because they are
 * deliberately framework-free. Everything that touches the container, config
 * or database — the plugin, the registry, the model trait — needs an
 * application, which is what Testbench provides.
 */
abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FilamentSeoServiceProvider::class,
        ];
    }

    /**
     * The package's own migration, plus the table the test model needs.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database');
    }
}
