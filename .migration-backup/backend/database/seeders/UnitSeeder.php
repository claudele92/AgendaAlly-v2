<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitTranslation;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Units. `id` is a stable, fixed natural key (matching the existing
        // RoleSeeder/SubscriptionSeeder convention) so re-running never
        // creates duplicates and existing product references stay valid.
        $units = [
            // Count
            ['id' => 1, 'title' => 'PCS'],
            ['id' => 2, 'title' => 'Unit'],
            ['id' => 3, 'title' => 'Pair'],
            ['id' => 4, 'title' => 'Dozen'],
            ['id' => 5, 'title' => 'Set'],
            // Weight
            ['id' => 6, 'title' => 'kg'],
            ['id' => 7, 'title' => 'g'],
            ['id' => 8, 'title' => 'mg'],
            ['id' => 9, 'title' => 'lb'],
            ['id' => 10, 'title' => 'oz'],
            ['id' => 11, 'title' => 'ton'],
            // Volume
            ['id' => 12, 'title' => 'L'],
            ['id' => 13, 'title' => 'mL'],
            ['id' => 14, 'title' => 'gal'],
            // Length
            ['id' => 15, 'title' => 'm'],
            ['id' => 16, 'title' => 'cm'],
            ['id' => 17, 'title' => 'mm'],
            ['id' => 18, 'title' => 'km'],
            ['id' => 19, 'title' => 'in'],
            ['id' => 20, 'title' => 'ft'],
            // Area
            ['id' => 21, 'title' => 'm²'],
            ['id' => 22, 'title' => 'ft²'],
            ['id' => 23, 'title' => 'ha'],
            // Time / service
            ['id' => 24, 'title' => 'min'],
            ['id' => 25, 'title' => 'hour'],
            ['id' => 26, 'title' => 'day'],
            ['id' => 27, 'title' => 'session'],
            // Packaging
            ['id' => 28, 'title' => 'box'],
            ['id' => 29, 'title' => 'pack'],
            ['id' => 30, 'title' => 'roll'],
            ['id' => 31, 'title' => 'bag'],
            ['id' => 32, 'title' => 'bottle'],
            ['id' => 33, 'title' => 'carton'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['id' => $unit['id']], [
                'active' => 1,
                'position' => 'after',
            ]);

            // Matched on the table's own unique key (unit_id, locale) rather
            // than a translation row id, so this stays idempotent even if
            // translation ids drift between runs.
            UnitTranslation::updateOrCreate(
                ['unit_id' => $unit['id'], 'locale' => 'en'],
                ['title' => $unit['title']]
            );
        }
    }
}
