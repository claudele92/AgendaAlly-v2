<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Explicit isolated migration, outside the frozen historical chain.
        // SQLite already stores unbounded TEXT in the existing varchar column;
        // preserve the original protected SQLite schema byte-for-byte.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('email_settings', function (Blueprint $table): void {
                $table->text('password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Ciphertext must never be truncated or restored to plaintext by rollback.
        throw new RuntimeException('SMTP credential storage narrowing is not a safe rollback.');
    }
};
