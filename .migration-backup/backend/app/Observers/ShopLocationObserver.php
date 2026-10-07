<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\ShopLocation;
use App\Traits\Loggable;
use Cache;

class ShopLocationObserver
{
    use Loggable;

    /**
     * Handle the Shop "creating" event.
     *
     * @param ShopLocation $shopLocation
     * @return void
     */
    public function creating(ShopLocation $shopLocation): void
    {
        Cache::flush();
    }

    /**
     * Handle the Shop "created" event.
     *
     * @param ShopLocation $shopLocation
     * @return void
     */
    public function created(ShopLocation $shopLocation): void
    {
        Cache::flush();

    }

    /**
     * Handle the Shop "updated" event.
     *
     * @param ShopLocation $shopLocation
     * @return void
     */
    public function updated(ShopLocation $shopLocation): void
    {
        Cache::flush();
    }

    /**
     * Handle the Shop "deleted" event.
     *
     * @param ShopLocation $shopLocation
     * @return void
     */
    public function deleted(ShopLocation $shopLocation): void
    {
        Cache::flush();
    }

}
