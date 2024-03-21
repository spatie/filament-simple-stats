<?php

namespace Spatie\FilamentSimpleStat;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\FilamentSimpleStat\Commands\FilamentSimpleStatCommand;

class FilamentSimpleStatServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-simple-stats')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament-simple-stats_table')
            ->hasCommand(FilamentSimpleStatCommand::class);
    }
}
