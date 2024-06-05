<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStats\SimpleStat;
use Spatie\FilamentSimpleStats\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    $period = CarbonPeriod::create('2022-01-24', '2024-03-25');

    foreach ($period as $date) {
        ExampleEvent::factory()->count(rand(0, 5))->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }
});

it('can set a period for the last 30 days', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last30Days();

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-01-24'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22')->endOfDay());

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(30);
});

it('can set a period for the last 7 days', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days();

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-02-16'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22')->endOfDay());

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(7);
});

it('can set a period for the last x days', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->lastDays(5);

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-02-18'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22')->endOfDay());

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(5);
});

it('can set a period for the last x months', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->lastMonths(2);

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2023-12-22'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22')->endOfDay());

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(63);
});

it('can set a period for the last x years', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->lastYears(2);

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2022-02-22'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22')->endOfDay());

    expect($simpleStat->dailyCount()->getChart())->toHaveCount(731);
});
