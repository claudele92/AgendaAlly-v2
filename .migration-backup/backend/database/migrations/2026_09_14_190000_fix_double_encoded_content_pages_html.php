<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Repairs Terms/Privacy/About content whose title or description ended up
 * with literal HTML entities (e.g. "Terms &amp; Conditions" displaying as
 * exactly that instead of "Terms & Conditions") instead of the plain
 * characters ContentPagesSeeder writes and the frontend expects (title
 * renders as plain text, description via dangerouslySetInnerHTML - neither
 * wants a pre-encoded string). Confirmed via direct Eloquent reads that a
 * fresh run of ContentPagesSeeder stores clean, undecoded text - this
 * migration exists for environments whose stored rows are already in the
 * bad state, from before this fix, regardless of exactly how they got
 * there. html_entity_decode() is idempotent and safe here: none of this
 * content is ever meant to display literal entity text, so decoding twice
 * on an already-clean row is a no-op, and the where() guards below mean a
 * clean install matches nothing at all.
 */
return new class extends Migration {
    private const TABLES = [
        'term_condition_translations',
        'privacy_policy_translations',
        'page_translations',
    ];

    private const COLUMNS = ['title', 'description'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            foreach (self::COLUMNS as $column) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where($column, 'like', '%&amp;%')
                    ->orWhere($column, 'like', '%&lt;%')
                    ->orWhere($column, 'like', '%&gt;%')
                    ->orWhere($column, 'like', '%&quot;%')
                    ->orWhere($column, 'like', '%&#039;%')
                    ->orderBy('id')
                    ->get(['id', $column])
                    ->each(function ($row) use ($table, $column) {
                        $decoded = html_entity_decode($row->$column, ENT_QUOTES | ENT_HTML5);

                        if ($decoded !== $row->$column) {
                            DB::table($table)->where('id', $row->id)->update([$column => $decoded]);
                        }
                    });
            }
        }
    }

    /**
     * Not reversible - we don't know which rows (if any) were legitimately
     * meant to contain literal entity text, so there's nothing safe to
     * restore here. A no-op down() is preferable to guessing.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
