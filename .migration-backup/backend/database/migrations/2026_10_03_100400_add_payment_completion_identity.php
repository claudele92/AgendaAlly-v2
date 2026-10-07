<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        (new \App\Services\PaymentAccounting\CompletionSchema)->up();
    }

    public function down(): void
    {
        (new \App\Services\PaymentAccounting\CompletionSchema)->down();
    }
};