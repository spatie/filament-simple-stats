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

it('can create a widget with monthly sum', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->lastYears(1)->monthlySum('score');

    expect($widget->getLabel())->toEqual('Total Scores');
    expect($widget->getDescription())->toEqual('Last 1 year(s)');
    expect($widget->getValue())->toBeString();
});

it('can create a widget with monthly average', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->lastYears(5)->monthlyAverage('score');

    expect($widget->getLabel())->toEqual('Average Monthly Score');
    expect($widget->getDescription())->toEqual('Last 5 year(s)');
    expect($widget->getValue())->toBeString();
});

it('can create a widget with yearly average', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->lastYears(5)->yearlyAverage('score');

    expect($widget->getLabel())->toEqual('Average Yearly Score');
    expect($widget->getDescription())->toEqual('Last 5 year(s)');
    expect($widget->getValue())->toBeString();
});

it('can create a widget with yearly count', function () {
    $widget = SimpleStat::make(ExampleEvent::class)->lastYears(5)->yearlyCount();

    expect($widget->getLabel())->toEqual('New Example Events');
    expect($widget->getDescription())->toEqual('Last 5 year(s)');
    expect($widget->getValue())->toBeString();
});

it('applies a where condition to the query', function () {
    $simpleStat = SimpleStat::make(ExampleEvent::class)->where('score', '>', 50);

    $query = $simpleStat->trend->builder->getQuery();

    $whereClause = collect($query->wheres)->firstWhere('column', 'score');

    expect($whereClause)->not->toBeNull();
    expect($whereClause['operator'])->toEqual('>');
    expect($whereClause['value'])->toEqual(50);

    $results = $simpleStat->trend->builder->get();

    foreach ($results as $result) {
        expect($result->score)->toBeGreaterThan(50);
    }
});
