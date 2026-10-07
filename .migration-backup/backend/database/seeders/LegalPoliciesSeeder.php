<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{Page, PrivacyPolicy, TermCondition};
use Database\Seeders\Support\LegalPolicyContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Shared fresh-install defaults; update only exact known seeded drafts. */
final class LegalPoliciesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $legacy = DevelopmentPreviewContentSeeder::legacyLegalDrafts();
            $this->document(TermCondition::class, 'Terms of Service', LegalPolicyContent::terms(), [
                $legacy['terms'], ContentPagesSeeder::legacyTermsHtml(),
            ]);
            $this->document(PrivacyPolicy::class, 'Privacy Policy', LegalPolicyContent::privacy(), [
                $legacy['privacy'], ContentPagesSeeder::legacyPrivacyHtml(),
            ]);
            $page = Page::query()->where('type', Page::REFUND_CANCELLATION)->first();
            if (!$page) {
                $page = Page::query()->create([
                    'type' => Page::REFUND_CANCELLATION, 'active' => true, 'buttons' => [],
                ]);
            }
            $this->translation($page, 'Refund & Cancellation Policy', LegalPolicyContent::refund(), []);
        });
    }

    private function document(string $model, string $title, string $html, array $legacy): void
    {
        $record = $model::query()->first() ?? $model::query()->create([]);
        $this->translation($record, $title, $html, $legacy);
    }

    private function translation(object $record, string $title, string $html, array $legacy): void
    {
        $translation = $record->translations()->where('locale', 'en')->first();
        if (!$translation) {
            $record->translations()->create(['locale' => 'en', 'title' => $title, 'description' => $html]);
            return;
        }
        if ($translation->description === $html) return;
        // Custom/Admin-edited documents and all other languages remain untouched.
        if (!in_array((string) $translation->description, $legacy, true)
            || !in_array((string) $translation->title, ['Terms & Conditions', 'Terms of Service', 'Privacy Policy'], true)) {
            return;
        }
        $translation->update(['title' => $title, 'description' => $html]);
    }
}
