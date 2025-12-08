<?php

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Spatie\FilamentSimpleStats\SimpleStat;
use Spatie\FilamentSimpleStats\Tests\Support\ExampleEvent;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    // Create data for current period (last 30 days: Jan 24 - Feb 22)
    $currentPeriod = CarbonPeriod::create('2024-01-24', '2024-02-22');
    foreach ($currentPeriod as $date) {
        ExampleEvent::factory()->count(5)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }

    // Create data for previous period (30 days before: Dec 25 - Jan 23)
    $previousPeriod = CarbonPeriod::create('2023-12-25', '2024-01-23');
    foreach ($previousPeriod as $date) {
        ExampleEvent::factory()->count(2)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }
});

it('shows upward trend by default when current period is higher', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->dailyCount();

    expect($widget->getColor())->toEqual('success');
    expect($widget->getDescriptionIcon())->toEqual('heroicon-m-arrow-trending-up');
    expect($widget->getDescription())->toContain('+');
    expect($widget->getDescription())->toContain('%');
});

it('can disable trends with withoutTrend', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->withoutTrend()->dailyCount();

    expect($widget->getColor())->toBeNull();
    expect($widget->getDescriptionIcon())->toBeNull();
    expect($widget->getDescription())->not->toContain('%');
    expect($widget->getDescription())->toEqual('Last 30 days');
});

it('shows downward trend when current period is lower', function () {
    // Clear existing data and create a downward trend scenario
    ExampleEvent::truncate();
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    // Current period (last 30 days: Jan 24 - Feb 22) - LOW data (2 per day)
    $currentPeriod = CarbonPeriod::create('2024-01-24', '2024-02-22');
    foreach ($currentPeriod as $date) {
        ExampleEvent::factory()->count(2)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }

    // Previous period (30 days before: Dec 25 - Jan 23) - HIGH data (10 per day)
    $previousPeriod = CarbonPeriod::create('2023-12-25', '2024-01-23');
    foreach ($previousPeriod as $date) {
        ExampleEvent::factory()->count(10)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }

    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->dailyCount();

    expect($widget->getColor())->toEqual('danger');
    expect($widget->getDescriptionIcon())->toEqual('heroicon-m-arrow-trending-down');
    expect($widget->getDescription())->toContain('-');
});

it('inverts colors when invertTrendColors is called', function () {
    // Clear existing data and create a downward trend scenario
    ExampleEvent::truncate();
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    // Current period (last 30 days: Jan 24 - Feb 22) - LOW data (2 per day)
    $currentPeriod = CarbonPeriod::create('2024-01-24', '2024-02-22');
    foreach ($currentPeriod as $date) {
        ExampleEvent::factory()->count(2)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }

    // Previous period (30 days before: Dec 25 - Jan 23) - HIGH data (10 per day)
    $previousPeriod = CarbonPeriod::create('2023-12-25', '2024-01-23');
    foreach ($previousPeriod as $date) {
        ExampleEvent::factory()->count(10)->create([
            'created_at' => $date->startOfDay()->addMinutes(rand(0, 1440 - 1)),
        ]);
    }

    // When inverted, a downward trend should show as success (green) because decrease is good
    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->invertTrendColors()->dailyCount();

    expect($widget->getColor())->toEqual('success');
    expect($widget->getDescriptionIcon())->toEqual('heroicon-m-arrow-trending-down');
});

it('works with sum aggregation', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->dailySum('score');

    expect($widget->getColor())->toBeIn(['success', 'danger']);
    expect($widget->getDescription())->toContain('%');
});

it('works with average aggregation', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->monthlyAverage('score');

    expect($widget->getColor())->toBeIn(['success', 'danger']);
    expect($widget->getDescription())->toContain('%');
});

it('works with different time periods', function () {
    // Set up data to ensure trends are calculated for both 7-day and 30-day periods
    ExampleEvent::truncate();
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    // Create 60 days of data with varying amounts to ensure trends
    // Last 30 days (current): Jan 24 - Feb 22
    $last30Days = CarbonPeriod::create('2024-01-24', '2024-02-22');
    foreach ($last30Days as $date) {
        // More data in the most recent week
        $count = $date->greaterThanOrEqualTo('2024-02-16') ? 8 : 5;
        ExampleEvent::factory()->count($count)->create([
            'created_at' => $date->startOfDay()->addHours(12),
        ]);
    }

    // Previous 30 days: Dec 25 - Jan 23
    $previous30Days = CarbonPeriod::create('2023-12-25', '2024-01-23');
    foreach ($previous30Days as $date) {
        ExampleEvent::factory()->count(2)->create([
            'created_at' => $date->startOfDay()->addHours(12),
        ]);
    }

    $widget7Days = SimpleStat::make(ExampleEvent::class)->last7Days()->dailyCount();
    $widget30Days = SimpleStat::make(ExampleEvent::class)->last30Days()->dailyCount();

    expect($widget7Days->getColor())->toBeIn(['success', 'danger']);
    expect($widget30Days->getColor())->toBeIn(['success', 'danger']);
});

it('preserves custom description when showing trends', function () {
    $widget = SimpleStat::make(ExampleEvent::class)
        ->last30Days()
        ->description('Custom period')
        ->dailyCount();

    expect($widget->getDescription())->toContain('Custom period');
    expect($widget->getDescription())->toContain('%');
});

it('shows no trend when values are equal', function () {
    // Clear existing data and create equal values for both periods
    ExampleEvent::truncate();
    Carbon::setTestNow(Carbon::parse('2024-02-22'));

    // Both periods get exactly 3 records per day (no randomness)
    $currentPeriod = CarbonPeriod::create('2024-01-24', '2024-02-22');
    foreach ($currentPeriod as $date) {
        ExampleEvent::factory()->count(3)->create([
            'created_at' => $date->startOfDay()->addHours(12),
            'score' => 100, // Fixed score to ensure sum/average are also equal
        ]);
    }

    $previousPeriod = CarbonPeriod::create('2023-12-25', '2024-01-23');
    foreach ($previousPeriod as $date) {
        ExampleEvent::factory()->count(3)->create([
            'created_at' => $date->startOfDay()->addHours(12),
            'score' => 100, // Fixed score to ensure sum/average are also equal
        ]);
    }

    $widget = SimpleStat::make(ExampleEvent::class)->last30Days()->dailyCount();

    // When values are equal (0% change), no trend indicator should be shown
    expect($widget->getColor())->toBeNull();
    expect($widget->getDescriptionIcon())->toBeNull();
});
