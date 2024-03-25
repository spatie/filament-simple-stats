<?php

/** @noinspection ALL */

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2024-02-22'));
});

it('can set a period for the last 30 days', function () {
    $simpleStat = \Spatie\FilamentSimpleStat\SimpleStat::make(User::class)->last30Days();

    expect($simpleStat->trend->start)->toEqual(Carbon::parse('2024-01-24'));
    expect($simpleStat->trend->end)->toEqual(Carbon::parse('2024-02-22'));
    expect($simpleStat->trend->start->diffInDays($simpleStat->trend->end))->toEqual(29);
});
