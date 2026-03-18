<?php

declare(strict_types=1);

namespace Spatie\FilamentSimpleStats\Support;

/** @internal */
final readonly class TrendValue
{
    public function __construct(
        public string $date,
        public int|float $aggregate,
    ) {}
}
