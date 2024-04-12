<?php

namespace Spatie\FilamentSimpleStats;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SimpleStat
{
    public Trend $trend;

    protected ?string $label;

    protected ?string $description;

    protected bool $overWriteDescription = false;

    public string $dateColumn = 'created_at';

    public ?string $aggregateColumn;

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
            start: now()->startOfDay()->subDays($days - 1),
            end: now()->startOfDay(),
        );

        if (! $this->overWriteDescription) {
            $this->description = __('Last :days days', ['days' => $days]);
        }

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

    public function hourlySum(string $column): Stat
    {
        $this->aggregateColumn = $column;

        $trendData = $this->trend->perHour()->sum('earnings');

        return $this->buildSumStat($trendData);
    }

    public function dailySum(string $column): Stat
    {
        $this->aggregateColumn = $column;

        $trendData = $this->trend->perDay()->sum('earnings');

        return $this->buildSumStat($trendData);
    }

    public function monthlySum(string $column): Stat
    {
        $this->aggregateColumn = $column;

        $trendData = $this->trend->perMonth()->sum('earnings');

        return $this->buildSumStat($trendData);
    }

    protected function buildCountStat(Collection $trendData): Stat
    {
        $total = $trendData->sum('aggregate');

        return $this->buildStat($total, $trendData, AggregateType::Count);
    }

    protected function buildAverageStat(Collection $trendData): Stat
    {
        $total = $trendData->average('aggregate');

        return $this->buildStat($total ?? '', $trendData, AggregateType::Average);
    }

    protected function buildSumStat(Collection $trendData): Stat
    {
        $total = $trendData->sum('aggregate');

        return $this->buildStat($total, $trendData, AggregateType::Sum);
    }

    protected function buildStat(int|float $faceValue, Collection $chartValues, AggregateType $aggregateType): Stat
    {
        return Stat::make($this->buildLabel($aggregateType), $this->formatFaceValue($faceValue))
            ->chart($chartValues->map(fn (TrendValue $trend) => $trend->aggregate)->toArray())
            ->description($this->description);
    }

    protected function formatFaceValue(int|float $total): string
    {
        if ($total > 1000) {
            return number_format($total / 1000, 2).'k';
        }

        return $total;
    }

    protected function buildLabel(AggregateType $aggregateType): string
    {
        if (isset($this->label)) {
            return $this->label;
        }

        $label = match ($aggregateType) {
            AggregateType::Average => 'Average ',
            AggregateType::Sum => 'Total ',
            default => '',
        };

        $label .= match($this->dateColumn) {
            'created_at' => 'new ',
            'updated_at' => 'updated ',
            'deleted_at' => 'deleted ',
            default => '',
        };

        $label .= $this->getEntityName();

        return ucwords($label);
    }

    protected function getEntityName(): string
    {
        if (! isset($this->aggregateColumn)) {
            return Str::plural(Str::title(Str::snake(class_basename($this->model), ' ')));
        }

        return Str::plural(Str::title(Str::snake($this->aggregateColumn, ' ')));
    }
}
