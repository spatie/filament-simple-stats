<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStats\SimpleStat;
use Spatie\FilamentSimpleStats\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    $period = CarbonPeriod::create('2022-01-24', '2024-03-25');

    foreach ($period as $date) {
        ExampleEvent::factory()->count(rand(0, 5))->create(['created_at' => $date]);
    }
});

it('can do daily counts', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->lastDays(2);

    ray($simpleStat);

    $simpleStat = $simpleStat->hourlyAverage();

    // Problem we get 48 hours, we would expect to get 72 hours.
    expect($simpleStat->getChart())->toHaveCount(48);
});
