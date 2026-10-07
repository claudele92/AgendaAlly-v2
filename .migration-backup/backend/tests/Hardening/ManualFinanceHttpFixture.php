<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use Illuminate\Support\Facades\{DB,Schema,Facade};
use Illuminate\Database\Schema\Blueprint;

/** Disposable HTTP-only authority; never loads .env or the normal application. */
abstract class ManualFinanceHttpFixture extends ManualFinancialWorkflowFixture
{
    protected function httpAuthority(string $directory): void
    {
        if (!preg_match('~/\.local/manual-finance/http-identity-[a-f0-9]{16}$~D',$directory)
            || is_link($directory) || !is_dir($directory)) throw new \RuntimeException('Owned fixture directory required.');
        $this->app->instance('files',new \Illuminate\Filesystem\Filesystem);
        config(['permission'=>require __DIR__.'/../../vendor/spatie/laravel-permission/config/permission.php']);
        config(['auth.guards.sanctum'=>['driver'=>'sanctum','provider'=>'users'],
            'sanctum.guard'=>['web'],'sanctum.expiration'=>null,
            'session'=>['driver'=>'file','files'=>$directory.'/sessions','cookie'=>'finance_fixture_session',
                'lifetime'=>120,'expire_on_close'=>false,'lottery'=>[0,100],'path'=>'/',
                'domain'=>null,'secure'=>false,'http_only'=>true,'same_site'=>'lax']]);
        $this->app->forgetInstance('auth');
        Facade::clearResolvedInstance('auth');
        $this->app->register(\Illuminate\Session\SessionServiceProvider::class);
        $this->app->register(\Illuminate\Cookie\CookieServiceProvider::class);
        $this->app->register(\Illuminate\Auth\AuthServiceProvider::class);
        $auth=$this->app['auth'];
        $auth->extend('sanctum',fn($app,$name,$config)=>new \Illuminate\Auth\RequestGuard(
            new \Laravel\Sanctum\Guard($auth,null,$config['provider']),
            $app['request'],$auth->createUserProvider($config['provider'])));
        $router=$this->app['router'];
        $router->aliasMiddleware('auth',\App\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('block.ip',\App\Http\Middleware\BlockIpMiddleware::class);
        // The original route file supplies the actual auth:sanctum finance routes.
        // File sessions are test-only transport setup, not a production API change.
        $router->group(['prefix'=>'api','middleware'=>[
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]],fn()=>require __DIR__.'/../../routes/api.php');
        \Illuminate\Http\Request::macro('validate',function(array $rules) {
            return \Illuminate\Support\Facades\Validator::make($this->all(),$rules)->validate();
        });
    }

    public function openHttpDatabase(string $directory): \Illuminate\Foundation\Application
    {
        parent::setUp(); // Builds only an independent in-memory schema.
        if (!is_file($directory.'/native.sqlite') || is_link($directory.'/native.sqlite')) {
            throw new \RuntimeException('Missing owned native fixture.');
        }
        config(['database.connections.hardening.database'=>$directory.'/native.sqlite']);
        DB::purge('hardening');
        DB::connection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
        $this->httpAuthority($directory);
        return $this->app;
    }

    protected function provisionIdentities(): void
    {
        Schema::table('users',function(Blueprint $t) {
            $t->string('email')->nullable(); $t->string('remember_token')->nullable(); $t->timestamps();
        });
        Schema::create('roles',function(Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('guard_name'); $t->timestamps();
        });
        Schema::create('personal_access_tokens',function(Blueprint $t) {
            $t->id(); $t->morphs('tokenable'); $t->string('name'); $t->string('token',64)->unique();
            $t->text('abilities')->nullable(); $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable(); $t->timestamps();
        });
        Schema::create('languages',function(Blueprint $t) {
            $t->id(); $t->string('locale'); $t->boolean('default')->default(false);
        });
        Schema::table('currencies',fn(Blueprint $t)=>$t->boolean('default')->default(false));
        Schema::create('shop_locations',function(Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('shop_id'); $t->unsignedBigInteger('country_id')->nullable();
        });
        DB::table('shop_locations')->insert(['shop_id'=>1,'country_id'=>1]);
        DB::table('countries')->insert(['id'=>2]);
        foreach ([4,5,6,7,8] as $id) DB::table('users')->insert(['id'=>$id]);
        foreach ([1=>'seller',2=>'admin',3=>'manager'] as $id=>$name) {
            DB::table('roles')->insert(['id'=>$id,'name'=>$name,'guard_name'=>'web']);
        }
        foreach ([1=>1,3=>2,4=>2,5=>3,6=>2,8=>3] as $user=>$role) {
            DB::table('model_has_roles')->insert(['model_id'=>$user,'model_type'=>User::class,'role_id'=>$role]);
        }
        // 4: structural admin without grants; 5: foreign admin with grants;
        // 6: second fully-granted operator; 8: invited country-role operator.
        foreach ([5,6] as $user) foreach (DB::table('permissions')->pluck('id') as $permission) {
            DB::table('model_has_permissions')->insert(['model_id'=>$user,'model_type'=>User::class,'permission_id'=>$permission]);
        }
        DB::table('country_admins')->insert([
            ['user_id'=>4,'country_id'=>1],['user_id'=>5,'country_id'=>2],
        ]);
        DB::table('country_roles')->insert(['id'=>1,'country_id'=>1]);
        DB::table('country_invitations')->insert(['user_id'=>8,'country_id'=>1,'country_role_id'=>1,'status'=>'accepted']);
        foreach (DB::table('permissions')->get() as $permission) {
            DB::table('country_permissions')->insert(['id'=>$permission->id,'key'=>$permission->name]);
            DB::table('country_role_permissions')->insert(['country_role_id'=>1,'country_permission_id'=>$permission->id]);
        }
    }

    protected function exactEffects(): array
    {
        $snapshot=[];
        // Auth last_used_at changes legitimately; all other database rows are retained.
        foreach (Schema::getTableListing() as $table) {
            $table=basename(str_replace('.','/',$table));
            if ($table==='personal_access_tokens' || $table==='sqlite_sequence') continue;
            $rows=DB::table($table)->get()->map(fn($r)=>(array)$r)->all();
            usort($rows,fn($a,$b)=>strcmp(serialize($a),serialize($b)));
            $snapshot[$table]=$rows;
        }
        ksort($snapshot);
        return $snapshot;
    }
}
