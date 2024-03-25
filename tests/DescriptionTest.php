<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStat\SimpleStat;
use Spatie\FilamentSimpleStat\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    $period = CarbonPeriod::create('2024-03-20', '2024-03-25');

    foreach ($period as $date) {
        ExampleEvent::factory()->count(rand(0, 2))->create(['created_at' => $date]);
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
