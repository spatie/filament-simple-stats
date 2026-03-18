<?php

declare(strict_types=1);

namespace Spatie\FilamentSimpleStats;

enum AggregateType
{
    case Count;
    case Average;
    case Sum;
}
