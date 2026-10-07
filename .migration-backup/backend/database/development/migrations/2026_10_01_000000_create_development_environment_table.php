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
        if (Schema::hasTable('agendaally_development_environment')) {
            throw new RuntimeException(
                'The development ownership table already exists outside the guarded bootstrap process.'
            );
        }

        $databasePath = DB::connection('sqlite')->getDatabaseName();
        $relativePath = str_replace('\\', '/', substr($databasePath, strlen(base_path()) + 1));
        $manifest = require database_path('development/manifest.php');

        Schema::create('agendaally_development_environment', function (Blueprint $table): void {
            $table->id();
            $table->string('environment', 16);
            $table->string('database_path', 255)->unique();
            $table->unsignedInteger('schema_version');
            $table->unsignedInteger('demo_seed_version')->default(0);
            $table->string('migration_set_sha256', 64);
            $table->timestamps();
        });

        DB::table('agendaally_development_environment')->insert([
            'environment' => 'local',
            'database_path' => $relativePath,
            'schema_version' => (int) $manifest['schema_version'],
            'demo_seed_version' => 0,
            'migration_set_sha256' => (string) $manifest['migration_set_sha256'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        throw new RuntimeException('The development database ownership marker is intentionally irreversible.');
    }
};