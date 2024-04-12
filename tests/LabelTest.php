<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStats\SimpleStat;
use Spatie\FilamentSimpleStats\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    $period = CarbonPeriod::create('2024-03-20', '2024-03-25');

    foreach ($period as $date) {
        ExampleEvent::factory()->count(rand(0, 2))->create(['created_at' => $date]);
    }
});

it('constructs a label for daily average', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days()->dailyAverage();

    expect($simpleStat->getLabel())->toBe('Average New Example Events');
});

it('constructs a label for monthly count', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days()->monthlyCount();

    expect($simpleStat->getLabel())->toBe('New Example Events');
});

it('uses different wording when the column type is updated_at', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->dateColumn('updated_at')->last7Days()->monthlyCount();

    expect($simpleStat->getLabel())->toBe('Updated Example Events');
});

it('does not overwrite a custom label', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->label('Custom Label')->last7Days()->monthlyCount();

    expect($simpleStat->getLabel())->toBe('Custom Label');
});

it('adapts for a custom aggregate column', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->last7Days()->dailySum('score');

    expect($simpleStat->getLabel())->toBe('Total New Scores');
});
