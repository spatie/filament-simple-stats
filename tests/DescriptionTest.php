<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStats\SimpleStat;
use Spatie\FilamentSimpleStats\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    $period = CarbonPeriod::create('2024-03-20', '2024-03-25');

    foreach ($period as $date) {
        ExampleEvent::factory()->count(rand(0, 2))->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }
});

it('constructs a description based on the interval', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days()->dailyAverage();
    expect($simpleStat->getDescription())->toBe('Last 7 days');

    $simpleStat = SimpleStat::make(ExampleEvent::class)->last30Days()->dailyAverage();
    expect($simpleStat->getDescription())->toBe('Last 30 days');

    $simpleStat = SimpleStat::make(ExampleEvent::class)->lastDays(14)->dailyAverage();
    expect($simpleStat->getDescription())->toBe('Last 14 days');
});

it('will not override a custom description', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->description('Custom description')->last7Days()->dailyAverage();
    expect($simpleStat->getDescription())->toBe('Custom description');
});
