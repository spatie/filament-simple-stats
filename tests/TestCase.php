<?php

namespace Spatie\FilamentSimpleStat\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\FilamentSimpleStat\Providers\FilamentSimpleStatServiceProvider;
use Spatie\FilamentSimpleStat\Tests\Support\TestServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_example_events_table.php';
        $migration->up();
    }
}
