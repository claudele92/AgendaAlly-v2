<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\User;
use Illuminate\Support\Facades\{DB,Schema};

/** Explicit grants, followed by native country/source restrictions. No Admin shortcut. */
final class FinanceScope
{
    public static function keys(): array
    {
        return ['payments.refunds.view','payments.refunds.request','payments.refunds.approve',
            'payments.refunds.complete','payments.refunds.reconcile','payments.refunds.evidence.view',
            'payments.refunds.vendor_direct.execute','payments.payouts.view','payments.payouts.approve',
            'payments.payouts.complete','payments.payouts.reconcile','payments.payouts.evidence.view'];
    }

    public function has(User $user, object $allocation, string $kind, string $action): bool
    {
        $key = 'payments.'.($kind === 'refund' ? 'refunds' : 'payouts').'.'.$action;
        if (!in_array($key,self::keys(),true) || !$user->hasRole(['admin','manager'])) return false;
        // Native bespoke country-role grants. No call to hasCountryPermission:
        // that intentionally grants every permission to structural country owners.
        $invited = DB::table('country_invitations as i')
            ->join('country_role_permissions as rp','rp.country_role_id','=','i.country_role_id')
            ->join('country_permissions as p','p.id','=','rp.country_permission_id')
            ->join('country_roles as r','r.id','=','i.country_role_id')
            ->where('i.user_id',$user->id)->where('i.country_id',$allocation->country_id)->where('i.status','accepted')
            ->where(fn($q)=>$q->whereNull('r.country_id')->orWhere('r.country_id',$allocation->country_id))
            ->where('p.key',$key)->exists();
        if ($invited) return $this->countryFootprint($allocation);
        // Existing Spatie direct/role permission tables are reusable, but only
        // when an actual grant exists and the native identity is in this country.
        $country = $user->countryAdmin?->country_id;
        if (!$user->isSuperAdmin() && (int)$country !== (int)$allocation->country_id) return false;
        if ($country !== null && !$this->countryFootprint($allocation)) return false;
        if (!Schema::hasTable('permissions')) return false;
        $permission = DB::table('permissions')->where('name',$key)->where('guard_name','web')->value('id');
        if (!$permission) return false;
        $type = $user->getMorphClass();
        return DB::table('model_has_permissions')->where('model_type',$type)->where('model_id',$user->id)->where('permission_id',$permission)->exists()
            || DB::table('model_has_roles as mr')->join('role_has_permissions as rp','rp.role_id','=','mr.role_id')
                ->where('mr.model_type',$type)->where('mr.model_id',$user->id)->where('rp.permission_id',$permission)->exists();
    }

    private function countryFootprint(object $a): bool
    {
        if (!isset($a->shop_id)) return false;
        return \App\Models\Shop::query()->whereKey($a->shop_id)->whollyInCountry((int)$a->country_id)->exists();
    }

    public function readable(User $u, object $a, string $kind): bool
    {
        return $this->has($u,$a,$kind,'view') || $this->has($u,$a,$kind,'approve')
            || $this->has($u,$a,$kind,'complete') || $this->has($u,$a,$kind,'reconcile')
            || ($kind==='refund' && $this->has($u,$a,$kind,'request'))
            || ($kind==='refund' && (int)$a->payer_user_id===(int)$u->id)
            || ($kind==='payout' && $this->vendor($u,$a));
    }

    public function vendor(User $u, object $a): bool
    {
        return (int)$a->vendor_user_id===(int)$u->id
            && DB::table('shops')->where('id',$a->shop_id)->where('user_id',$u->id)->exists()
            && $u->hasShopPermission((int)$a->shop_id,'payments.payouts.manage');
    }

    public function require(User $u, object $a, string $kind, string $action): void
    {
        if (!$this->has($u,$a,$kind,$action)) abort(403,'Explicit scoped financial permission required.');
    }
}
