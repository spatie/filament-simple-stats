<?php

declare(strict_types=1);

namespace Spatie\FilamentSimpleStats;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\FilamentSimpleStats\Support\Trend;
use Spatie\FilamentSimpleStats\Support\TrendValue;

class SimpleStat
{
    public Trend $trend;

    protected ?string $label = null;

    protected ?string $description = null;

    protected bool $overWriteDescription = false;

    public string $dateColumn = 'created_at';

    public ?string $aggregateColumn = null;

    protected bool $showTrend = true;

    protected bool $invertTrendColors = false;

    protected ?Carbon $periodStart = null;

    protected ?Carbon $periodEnd = null;

    protected ?string $periodType = null;

    protected ?int $periodLength = null;

    protected ?string $periodGrouping = null;

    public function __construct(private readonly string $model)
    {
        $this->trend = Trend::model($model)->dateColumn($this->dateColumn);
    }

    public static function make(string $model): self
    {
        return new self($model);
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        $this->overWriteDescription = true;

        return $this;
    }

    public function dateColumn(string $dateColumn): self
    {
        $this->trend->dateColumn($dateColumn);
        $this->dateColumn = $dateColumn;

        return $this;
    }

    public function where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): self
    {
        $this->trend->builder->where($column, $operator, $value, $boolean);

        return $this;
    }

    public function withoutTrend(): self
    {
        $this->showTrend = false;

        return $this;
    }

    public function invertTrendColors(): self
    {
        $this->invertTrendColors = true;

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
        $this->periodStart = now()->startOfDay()->subDays($days - 1);
        $this->periodEnd = now()->endOfDay();
        $this->periodType = 'days';
        $this->periodLength = $days;

        $this->trend->between(
            start: $this->periodStart,
            end: $this->periodEnd,
        );

        if (! $this->overWriteDescription) {
            $this->description = __('Last :days days', ['days' => $days]);
        }

        return $this;
    }

    public function lastMonths(int $months): self
    {
        $this->periodStart = now()->subMonths($months);
        $this->periodEnd = now()->endOfDay();
        $this->periodType = 'months';
        $this->periodLength = $months;

        $this->trend->between(
            start: $this->periodStart,
            end: $this->periodEnd,
        );

        if (! $this->overWriteDescription) {
            $this->description = __('Last :months months', ['months' => $months]);
        }

        return $this;
    }

    public function lastYears(int $years): self
    {
        $this->periodStart = now()->subYears($years);
        $this->periodEnd = now()->endOfDay();
        $this->periodType = 'years';
        $this->periodLength = $years;

        $this->trend->between(
            start: $this->periodStart,
            end: $this->periodEnd,
        );

        if (! $this->overWriteDescription) {
            $this->description = __('Last :years year(s)', ['years' => $years]);
        }

        return $this;
    }

    public function dailyCount(): Stat
    {
        $this->periodGrouping = 'day';

        return $this->buildCountStat($this->trend->perDay()->count());
    }

    public function monthlyCount(): Stat
    {
        $this->periodGrouping = 'month';

        return $this->buildCountStat($this->trend->perMonth()->count());
    }

    public function yearlyCount(): Stat
    {
        $this->periodGrouping = 'year';

        return $this->buildCountStat($this->trend->perYear()->count());
    }

    public function dailyAverage(): Stat
    {
        $this->periodGrouping = 'day';

        return $this->buildAverageStat($this->trend->perDay()->count());
    }

    public function monthlyAverage(string $column): Stat
    {
        $this->periodGrouping = 'month';
        $this->label('Average Monthly '.Str::title(Str::snake($column, ' ')));

        return $this->buildAverageStat($this->trend->perMonth()->average($column));
    }

    public function yearlyAverage(string $column): Stat
    {
        $this->periodGrouping = 'year';
        $this->label('Average Yearly '.Str::title(Str::snake($column, ' ')));

        return $this->buildAverageStat($this->trend->perYear()->average($column));
    }

    public function dailySum(string $column): Stat
    {
        $this->periodGrouping = 'day';
        $this->aggregateColumn = $column;

        return $this->buildSumStat($this->trend->perDay()->sum($column));
    }

    public function monthlySum(string $column): Stat
    {
        $this->periodGrouping = 'month';
        $this->aggregateColumn = $column;

        return $this->buildSumStat($this->trend->perMonth()->sum($column));
    }

    /** @param Collection<int, TrendValue> $trendData */
    protected function buildCountStat(Collection $trendData): Stat
    {
        $total = $trendData->sum('aggregate');

        return $this->buildStat($total, $trendData, AggregateType::Count);
    }

    /** @param Collection<int, TrendValue> $trendData */
    protected function buildAverageStat(Collection $trendData): Stat
    {
        $total = $trendData->average('aggregate');

        return $this->buildStat($total ?? '', $trendData, AggregateType::Average);
    }

    /** @param Collection<int, TrendValue> $trendData */
    protected function buildSumStat(Collection $trendData): Stat
    {
        $total = $trendData->sum('aggregate');

        return $this->buildStat($total, $trendData, AggregateType::Sum);
    }

    protected function calculatePreviousPeriodValue(AggregateType $aggregateType): int|float
    {
        if (! $this->periodStart || ! $this->periodEnd || ! $this->periodType || ! $this->periodLength || ! $this->periodGrouping) {
            return 0;
        }

        $previousStart = match ($this->periodType) {
            'days' => (clone $this->periodStart)->subDays($this->periodLength),
            'months' => (clone $this->periodStart)->subMonths($this->periodLength),
            'years' => (clone $this->periodStart)->subYears($this->periodLength),
            default => $this->periodStart,
        };

        $previousEnd = match ($this->periodType) {
            'days' => (clone $this->periodEnd)->subDays($this->periodLength),
            'months' => (clone $this->periodEnd)->subMonths($this->periodLength),
            'years' => (clone $this->periodEnd)->subYears($this->periodLength),
            default => $this->periodEnd,
        };

        $previousTrend = Trend::model($this->model)
            ->dateColumn($this->dateColumn)
            ->between(start: $previousStart, end: $previousEnd);

        $previousTrend = match ($this->periodGrouping) {
            'day' => $previousTrend->perDay(),
            'month' => $previousTrend->perMonth(),
            'year' => $previousTrend->perYear(),
            default => $previousTrend->perDay(),
        };

        $previousData = match ($aggregateType) {
            AggregateType::Count => $previousTrend->count(),
            AggregateType::Sum => $previousTrend->sum($this->aggregateColumn),
            AggregateType::Average => isset($this->aggregateColumn)
                ? $previousTrend->average($this->aggregateColumn)
                : $previousTrend->count(),
        };

        return match ($aggregateType) {
            AggregateType::Average => $previousData->average('aggregate') ?? 0,
            default => $previousData->sum('aggregate'),
        };
    }

    /** @param Collection<int, TrendValue> $chartValues */
    protected function buildStat(int|float|string $faceValue, Collection $chartValues, AggregateType $aggregateType): Stat
    {
        $stat = Stat::make($this->buildLabel($aggregateType), $this->formatFaceValue($faceValue))
            ->chart($chartValues->map(fn (TrendValue $trend) => $trend->aggregate)->toArray())
            ->description($this->description);

        if (! $this->showTrend) {
            return $stat;
        }

        $previousValue = $this->calculatePreviousPeriodValue($aggregateType);

        if ($previousValue == 0 && $faceValue == 0) {
            return $stat;
        }

        if ($previousValue == 0) {
            $percentageChange = 100;
        } else {
            $percentageChange = (($faceValue - $previousValue) / $previousValue) * 100;
        }

        if ($percentageChange == 0) {
            return $stat;
        }

        $isUpward = $percentageChange > 0;
        $isGood = $this->invertTrendColors ? ! $isUpward : $isUpward;

        $color = $isGood ? 'success' : 'danger';
        $icon = $isUpward ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        $formattedPercentage = number_format(abs($percentageChange), 1).'%';
        $description = ($percentageChange > 0 ? '+' : '-').$formattedPercentage;

        if ($this->description) {
            $description = "{$this->description} ({$description})";
        }

        return $stat
            ->description($description)
            ->descriptionIcon($icon)
            ->color($color);
    }

    protected function formatFaceValue(int|float|string $total): string
    {
        if (is_string($total)) {
            return $total;
        }

        if ($total > 1000) {
            return number_format($total / 1000, 2).'k';
        }

        return (string) $total;
    }

    protected function buildLabel(AggregateType $aggregateType): string
    {
        if ($this->label !== null) {
            return $this->label;
        }

        $label = match ($aggregateType) {
            AggregateType::Average => 'Average ',
            AggregateType::Sum => 'Total ',
            default => '',
        };

        if ($aggregateType !== AggregateType::Sum && $aggregateType !== AggregateType::Average) {
            $label .= match ($this->dateColumn) {
                'created_at' => 'new ',
                'updated_at' => 'updated ',
                'deleted_at' => 'deleted ',
                default => '',
            };
        }

        $label .= $this->getEntityName();

        return ucwords($label);
    }

    protected function getEntityName(): string
    {
        if ($this->aggregateColumn === null) {
            return Str::plural(Str::title(Str::snake(class_basename($this->model), ' ')));
        }

        return Str::plural(Str::title(Str::snake($this->aggregateColumn, ' ')));
    }
}
