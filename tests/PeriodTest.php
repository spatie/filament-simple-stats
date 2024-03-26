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

it('can set a period for the last 30 days', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last30Days();

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-01-24'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22'));

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(30);
});

it('can set a period for the last 7 days', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days();

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-02-16'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22'));

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(7);
});
