<?php

namespace Spatie\FilamentSimpleStat;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;

class SimpleStat
{
    public Trend $trend;

    public function __construct(private readonly string $title, string $model)
    {
        $this->trend = Trend::model($model);
    }

    public static function make(string $title, string $model): self
    {
        return new self($title, $model);
    }

    public function dateColumn(string $dateColumn): self
    {
        $this->trend->dateColumn($dateColumn);

        return $this;
    }

    public function last7Days(): self
    {
        return $this->lastDays(7);
    }

    public function last30Days(): self
    {
        return $this->lastDays(30);
    }

    public function lastDays(int $days): self
    {
        $this->trend->between(
            start: now()->subDays($days - 1),
            end: now(),
        );

        return $this;
    }

    public function hourlyCount(): Stat
    {
        return $this->buildCountStat($this->trend->perHour()->count());
    }

    public function dailyCount(): Stat
    {
        return $this->buildCountStat($this->trend->perDay()->count());
    }

    public function monthlyCount(): Stat
    {
        return $this->buildCountStat($this->trend->perMonth()->count());
    }

    public function yearlyCount(): Stat
    {
        return $this->buildCountStat($this->trend->perYear()->count());
    }

    public function hourlyAverage(): Stat
    {
        return $this->buildAverageStat($this->trend->perHour()->count());
    }

    public function dailyAverage(): Stat
    {
        return $this->buildAverageStat($this->trend->perDay()->count());
    }

    public function monthlyAverage(): Stat
    {
        return $this->buildAverageStat($this->trend->perMonth()->count());
    }

    public function yearlyAverage(): Stat
    {
        return $this->buildAverageStat($this->trend->perYear()->count());
    }

    private function buildCountStat(Collection $trendData): Stat
    {
        $total = $trendData->sum('aggregate');
        return $this->buildStat($total, $trendData);
    }

    private function buildAverageStat(Collection $trendData): Stat
    {
        $total = $trendData->average('aggregate');
        return $this->buildStat($total, $trendData);
    }

    private function buildStat(string $faceValue, Collection $chartValues): Stat
    {
        return Stat::make($this->title, $faceValue)
            ->chart($chartValues->map(fn (TrendValue $trend) => $trend->aggregate)->toArray());
    }
}
