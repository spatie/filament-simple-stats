<?php

namespace Spatie\FilamentSimpleStats\Tests\Support;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExampleEventFactory extends Factory
{
    protected $model = ExampleEvent::class;

    public function definition(): array
    {
        return [
            'score' => $this->faker->numberBetween(1, 100),
        ];
    }
}
