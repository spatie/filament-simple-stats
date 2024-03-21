<?php

namespace Spatie\FilamentSimpleStat\Commands;

use Illuminate\Console\Command;

class FilamentSimpleStatCommand extends Command
{
    public $signature = 'filament-simple-stats';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
