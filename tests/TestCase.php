<?php

namespace Spatie\FilamentSimpleStat\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\FilamentSimpleStat\Providers\FilamentSimpleStatServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }
}
