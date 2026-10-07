<?php
declare(strict_types=1);

use App\Support\SystemEmailTemplates;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        SystemEmailTemplates::ensure();
    }

    public function down(): void
    {
        // Records may have been customized since provisioning. Never erase them.
    }
};
