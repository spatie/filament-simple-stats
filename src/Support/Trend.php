<?php

declare(strict_types=1);

namespace Spatie\FilamentSimpleStats\Support;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/** @internal */
final class Trend
{
    public CarbonInterface $start;

    public CarbonInterface $end;

    private string $interval;

    private string $dateColumn = 'created_at';

    public function __construct(public Builder $builder) {}

    public static function model(string $model): self
    {
        return new self($model::query());
    }

    public function dateColumn(string $column): self
    {
        $this->dateColumn = $column;

        return $this;
    }

    public function between(CarbonInterface $start, CarbonInterface $end): self
    {
        $this->start = $start;
        $this->end = $end;

        return $this;
    }

    public function perDay(): self
    {
        $this->interval = 'day';

        return $this;
    }

    public function perMonth(): self
    {
        $this->interval = 'month';

        return $this;
    }

    public function perYear(): self
    {
        $this->interval = 'year';

        return $this;
    }

    /** @return Collection<int, TrendValue> */
    public function count(string $column = '*'): Collection
    {
        return $this->aggregate($column, 'count');
    }

    /** @return Collection<int, TrendValue> */
    public function sum(string $column): Collection
    {
        return $this->aggregate($column, 'sum');
    }

    /** @return Collection<int, TrendValue> */
    public function average(string $column): Collection
    {
        return $this->aggregate($column, 'avg');
    }

    /** @return Collection<int, TrendValue> */
    private function aggregate(string $column, string $function): Collection
    {
        $dateAlias = 'date';

        $values = $this->builder
            ->toBase()
            ->selectRaw("{$this->formatDateForDriver()} as {$dateAlias}, {$function}({$column}) as aggregate")
            ->whereBetween($this->dateColumn, [$this->start, $this->end])
            ->groupBy($dateAlias)
            ->orderBy($dateAlias)
            ->get();

        return $this->fillGaps($values, $dateAlias);
    }

    /** @return Collection<int, TrendValue> */
    private function fillGaps(Collection $values, string $dateAlias): Collection
    {
        $values = $values->map(fn ($value) => new TrendValue(
            date: $value->{$dateAlias},
            aggregate: $value->aggregate,
        ));

        $placeholders = collect(CarbonPeriod::between($this->start, $this->end)->interval("1 {$this->interval}"))
            ->map(fn (CarbonInterface $date) => new TrendValue(
                date: $date->format($this->carbonDateFormat()),
                aggregate: 0,
            ));

        return $values
            ->merge($placeholders)
            ->unique('date')
            ->sort()
            ->flatten();
    }

    private function formatDateForDriver(): string
    {
        /** @var Connection $connection */
        $connection = $this->builder->getConnection();
        $driver = $connection->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => $this->mysqlFormat(),
            'pgsql' => $this->pgsqlFormat(),
            'sqlite' => $this->sqliteFormat(),
            default => throw new InvalidArgumentException("Unsupported database driver: {$driver}"),
        };
    }

    private function mysqlFormat(): string
    {
        $format = match ($this->interval) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new InvalidArgumentException("Unsupported interval: {$this->interval}"),
        };

        return "date_format({$this->dateColumn}, '{$format}')";
    }

    private function pgsqlFormat(): string
    {
        $format = match ($this->interval) {
            'day' => 'YYYY-MM-DD',
            'month' => 'YYYY-MM',
            'year' => 'YYYY',
            default => throw new InvalidArgumentException("Unsupported interval: {$this->interval}"),
        };

        return "to_char(\"{$this->dateColumn}\", '{$format}')";
    }

    private function sqliteFormat(): string
    {
        $format = match ($this->interval) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new InvalidArgumentException("Unsupported interval: {$this->interval}"),
        };

        return "strftime('{$format}', {$this->dateColumn})";
    }

    private function carbonDateFormat(): string
    {
        return match ($this->interval) {
            'day' => 'Y-m-d',
            'month' => 'Y-m',
            'year' => 'Y',
            default => throw new InvalidArgumentException("Unsupported interval: {$this->interval}"),
        };
    }
}
