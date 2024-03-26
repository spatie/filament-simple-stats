<?php

namespace Spatie\FilamentSimpleStats\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExampleEvent extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ExampleEventFactory::new();
    }
}
