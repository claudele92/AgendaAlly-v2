<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DevelopmentCountryPaymentPolicySeeder;
use Illuminate\Console\Command;
use Throwable;

class SeedDevelopmentCountryPaymentPolicy extends Command
{
    protected $signature = 'development:payment-country-policy';

    protected $description = 'Apply only the guarded development country/payment assignments.';

    public function handle(): int
    {
        try {
            $result = $this->call('db:seed', [
                '--class' => DevelopmentCountryPaymentPolicySeeder::class,
                '--force' => true,
            ]);

            return $result === self::SUCCESS ? self::SUCCESS : self::FAILURE;
        } catch (Throwable) {
            $this->error(
                'Development payment-policy assignment stopped safely. Confirm explicit local opt-in and the owned SQLite marker.'
            );

            return self::FAILURE;
        }
    }
}