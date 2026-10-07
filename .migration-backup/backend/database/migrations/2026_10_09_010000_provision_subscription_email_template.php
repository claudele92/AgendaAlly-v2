<?php
declare(strict_types=1);

use App\Support\SystemEmailTemplates;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // No schema change, subscribers, campaign or delivery side effects.
        SystemEmailTemplates::ensure();
    }

    public function down(): void
    {
        // Never erase presentation which an Admin may have customized.
    }
};
