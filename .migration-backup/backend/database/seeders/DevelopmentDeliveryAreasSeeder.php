<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Area;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Additive neighbourhood examples; never prices, delivery permissions or orders. */
final class DevelopmentDeliveryAreasSeeder extends Seeder
{
    public function run(): void
    {
        DevelopmentDatabaseGuard::requireOptIn((string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'), env('DEVELOPMENT_MODE'));
        $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(base_path(), (string) config('database.default'),
            (array) config('database.connections.sqlite', []));
        if (!DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
            throw new RuntimeException('Delivery Area examples require the owned local development database.');
        }
        $added = 0;
        DB::transaction(function () use (&$added): void {
            foreach (['Douala' => ['Akwa', 'Bonapriso'], 'Yaoundé' => ['Bastos', 'Essos'],
                'Bafoussam' => ['Djeleng', 'Tamdja']] as $cityName => $names) {
                $cities = City::whereHas('translation', fn ($q) => $q->where('locale', 'en')->where('title', $cityName))
                    ->whereHas('country.translation', fn ($q) => $q->where('locale', 'en')->where('title', 'Cameroon'))
                    ->with('country')->get();
                if ($cities->count() !== 1) continue;
                $city = $cities->first();
                foreach ($names as $name) {
                    if (Area::where('city_id', $city->id)->whereHas('translations',
                        fn ($q) => $q->where('title', $name))->exists()) continue;
                    $area = Area::create(['city_id' => $city->id, 'country_id' => $city->country_id,
                        'region_id' => $city->region_id ?: $city->country?->region_id, 'active' => true]);
                    $area->translations()->create(['locale' => 'en', 'title' => $name]);
                    $added++;
                }
            }
        });
        $this->command?->info("Added $added local demo neighbourhoods; existing Areas and tariffs were preserved.");
    }
}