<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralResource;
use App\Models\Order;
use App\Models\Referral;
use App\Models\Settings;
use App\Models\Translation;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettingController extends Controller
{
    use ApiResponse;

    public function settingsInfo(): JsonResponse
    {
        $settings = \App\Helpers\MapsConfiguration::settings(Settings::get());

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), $settings);
    }

    public function referral(): JsonResponse
    {
        $active = Settings::where('key', 'referral_active')->first();

        if (!data_get($active, 'value')) {
            return $this->onErrorResponse([
                'code' => ResponseError::ERROR_404
            ]);
        }

        $referral = Referral::with([
            'translation' => fn($query) => $query->where('locale', $this->language),
            'translations',
            'galleries',
        ])->where([
            ['expired_at', '>=', now()],
        ])->first();

        if (empty($referral)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ReferralResource::make($referral)
        );
    }

    public function translationsPaginate(): JsonResponse
    {
        $translations = Cache::remember("language-$this->language", 86400, function () {
            return Translation::where('locale', $this->language)
                ->where('status', 1)
                ->pluck('value', 'key');
        });

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            $translations->all()
        );
    }

    /**
     * @return JsonResponse
     */
    public function systemInformation(): JsonResponse
    {
        $mysql  = DB::selectOne('SHOW VARIABLES LIKE "%innodb_version%"');

        try {
            $node       = exec('node -v');
            $npm        = exec('npm -v');
            $composer   = exec('composer -V');
        } catch (Throwable) {
            $node = '';
            $npm = '';
            $composer = '';
        }

        return $this->successResponse('success', [
            'PHP Version'       => @phpversion() ?? '8+',
            'Laravel Version'   => app()->version() ?? '8+',
            'OS Version'        => php_uname() ?? '20+',
            'MySql Version'     => @data_get($mysql, 'Value', '8.0') ?? '8.0',
            'NodeJs Version'    => $node,
            'NPM Version'       => $npm,
            'Project Version'   => env('PROJECT_V'),
            'Composer Version'  => $composer,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function stat(): JsonResponse
    {
        $users = DB::table('users')
            ->select(['id'])
            ->count('id');

        $orders = DB::table('orders')
            ->select(['id', 'status'])
            ->where('status', Order::STATUS_DELIVERED)
            ->count('id');

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
            'users'  => $users,
            'orders' => $orders
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function projectVersion(): JsonResponse
    {
        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
            'version' => env('PROJECT_V')
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function timeZone(): JsonResponse
    {
        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
            'timeZone' => config('app.timezone'),
            'time'     => date('Y-m-d H:i:s')
        ]);
    }
}
