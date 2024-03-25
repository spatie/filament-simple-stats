<?php

namespace Spatie\FilamentSimpleStat;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimpleStat
{
    private Trend $trend;

    public function __construct(private readonly string $model)
    {
        $this->trend = Trend::model($model);
    }

    public static function make(string $model): self
    {
        return new self($model);
    }

    public function dateColumn(string $dateColumn): self
    {
        $this->trend->dateColumn($dateColumn);

        return $this;
    }

    public function last30Days(): self
    {
        $this->trend->between(
            start: now()->subDays(29),
            end: now(),
        );

        return $this;
    }

    public function countPerDay(): Stat
    {
        $perDayTrend = $this->trend->perDay()->count();
        $total = $perDayTrend->sum('aggregate');

        return $this->buildStat($total, $perDayTrend, __('Last 30 days'));
    }

    private function buildStat(string $faceValue, Collection $chartValues, string $description = ''): Stat
    {
        return Stat::make($this->getEntityTitleFromModel(), $faceValue)
            ->chart($chartValues->map(fn (TrendValue $trend) => $trend->aggregate)->toArray())
            ->description($description);
    }

    private function getEntityTitleFromModel(): string
    {
        $pieces = explode('\\', $this->model);
        $entity = Str::plural(end($pieces));

        return __('New :entity', ['entity' => $entity]);
    }
}
