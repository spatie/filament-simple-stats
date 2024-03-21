<?php

namespace Spatie\FilamentSimpleStat\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\FilamentSimpleStat\FilamentSimpleStat
 */
class FilamentSimpleStat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Spatie\FilamentSimpleStat\FilamentSimpleStat::class;
    }
}
