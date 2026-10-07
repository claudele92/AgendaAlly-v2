<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\CarServiceTaxonomySeeder;
use Illuminate\Console\Command;
use Throwable;

class SeedDevelopmentCarServiceTaxonomy extends Command
{
    protected $signature = 'development:car-service-taxonomy';

    protected $description = 'Seed only the Car Service category tree in the guarded development database.';

    public function handle(): int
    {
        try {
            $result = $this->call('db:seed', [
                '--class' => CarServiceTaxonomySeeder::class,
                '--force' => true,
            ]);

            return $result === self::SUCCESS ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Car Service taxonomy seeding stopped safely: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }
}