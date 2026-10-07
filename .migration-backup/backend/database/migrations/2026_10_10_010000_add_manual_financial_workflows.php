<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB,Schema};
use App\Services\ManualFinance\{ManualSchema,FinanceScope};

return new class extends Migration {
    public function up(): void
    {
        (new ManualSchema)->up();
        foreach (FinanceScope::keys() as $key) {
            DB::table('country_permissions')->insertOrIgnore(['key'=>$key,'group'=>'payments','label'=>ucwords(str_replace('.',' ',$key))]);
            if (Schema::hasTable('permissions')) {
                DB::table('permissions')->insertOrIgnore(['name'=>$key,'guard_name'=>'web','created_at'=>now(),'updated_at'=>now()]);
            }
        }
        // Definitions only. No existing role receives an automatic Finance grant.
    }
    public function down(): void
    {
        (new ManualSchema)->down();
        // Retain permission definitions: removing an existing grant is a separate decision.
    }
};
