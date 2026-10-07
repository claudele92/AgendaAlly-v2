<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Country;
use App\Services\CountryRoleService\CountryRoleService;

class CountryObserver
{
    /**
     * Handle the Country "created" event — gives every new country the
     * standard set of country staff roles (see DefaultCountryRoles). Only
     * fires once, on creation; a subsequent update() call on the same
     * model (e.g. CountryService::create()'s follow-up image update)
     * never re-fires this.
     *
     * @param Country $model
     * @return void
     */
    public function created(Country $model): void
    {
        (new CountryRoleService())->seedDefaultRoles($model);
    }

    /**
     * Handle the Brand "updated" event.
     *
     * @param Country $model
     * @return void
     */
    public function updated(Country $model): void
    {
        $model->cities()->update(['region_id' => $model->region_id]);
        $model->areas()->update(['region_id'  => $model->region_id]);

        RegionRelationsObserver::country($model);
    }

}
