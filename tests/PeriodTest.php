<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStat\SimpleStat;
use Spatie\FilamentSimpleStat\Tests\Support\ExampleEvent;

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
    expect($simpleStat->trend->start->diffInDays($simpleStat->trend->end))->toEqual(29);

    expect($simpleStat->countPerDay()->getChart())->toHaveCount(30);
});
