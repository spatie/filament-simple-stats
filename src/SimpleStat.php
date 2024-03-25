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

    public function dailyCount(): Stat
    {
        $perDayTrend = $this->trend->perDay()->count();
        $total = $perDayTrend->sum('aggregate');

        return $this->buildStat($total, $perDayTrend);
    }

    private function buildStat(string $faceValue, Collection $chartValues): Stat
    {
        return Stat::make($this->title, $faceValue)
            ->chart($chartValues->map(fn (TrendValue $trend) => $trend->aggregate)->toArray());
    }
}
