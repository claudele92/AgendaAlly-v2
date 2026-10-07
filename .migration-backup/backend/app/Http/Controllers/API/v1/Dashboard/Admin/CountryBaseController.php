<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\CountryContext;

/**
 * Base for the country-scoped admin controllers (roles, staff, invites).
 * Resolves which country the current request operates against: an
 * assigned country admin (or their accepted staff) is locked to their own
 * country regardless of any `country_id` input; a global superadmin isn't
 * tied to one, so they must name which country they mean via `country_id`.
 */
abstract class CountryBaseController extends AdminBaseController
{
    protected ?int $countryId;

    public function __construct()
    {
        parent::__construct();

        $this->countryId = CountryContext::activeCountryId();
    }
}
