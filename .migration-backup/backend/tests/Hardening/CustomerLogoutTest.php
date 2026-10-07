<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Auth\LoginController;
use App\Http\Requests\FilterParamsRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
final class CustomerLogoutTest extends IsolatedTestCase
{
    private ?string $fixtureDatabase = null;

    protected function isolatedDatabaseConfiguration(): array
    {
        return ['driver'=>'sqlite','database'=>$this->fixtureDatabase ?? ':memory:','prefix'=>''];
    }

    public function startHttpFixture(string $path): \Illuminate\Foundation\Application
    {
        if (!str_starts_with($path, '/tmp/customer-logout-')) {
            throw new \RuntimeException('Isolated fixture path required');
        }
        if (is_link($path)) throw new \RuntimeException('Unsafe fixture path');
        if (!is_file($path)) {
            $stream=fopen($path,'xb'); fclose($stream); chmod($path,0600);
        }
        $this->fixtureDatabase = $path;
        $this->setUp();
        return $this->app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('auth', [
            'defaults' => ['guard' => 'web', 'passwords' => 'users'],
            'guards' => ['web' => ['driver' => 'session', 'provider' => 'users'],
                'sanctum' => ['driver' => 'sanctum', 'provider' => 'users']],
            'providers' => ['users' => ['driver' => 'eloquent', 'model' => User::class]],
        ]);
        Config::set('sanctum.guard', []);
        Config::set('session.path','/');
        Config::set('session.domain',null);
        Config::set('session.secure',false);
        $this->app->register(\Illuminate\Session\SessionServiceProvider::class);
        $this->app->register(\Illuminate\Cookie\CookieServiceProvider::class);
        $this->app->register(\Illuminate\Auth\AuthServiceProvider::class);
        // Use Sanctum's actual guard without booting its HTTP-kernel/publishing
        // machinery into the intentionally minimal isolated application.
        $auth = $this->app['auth'];
        $auth->extend('sanctum', function ($app, $name, array $config) use ($auth) {
            return new \Illuminate\Auth\RequestGuard(
                new \Laravel\Sanctum\Guard($auth, null, $config['provider']),
                $app['request'], $auth->createUserProvider($config['provider'])
            );
        });
        if (!Schema::hasTable('users')) {
        Schema::create('users', function ($t): void {
            $t->increments('id'); $t->string('email'); $t->text('firebase_token')->nullable();
            $t->string('remember_token')->nullable(); $t->timestamps();
        });
        Schema::create('personal_access_tokens', function ($t): void {
            $t->increments('id'); $t->morphs('tokenable'); $t->string('name');
            $t->string('token',64)->unique(); $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable(); $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
        Schema::create('languages', function ($t): void {
            $t->increments('id'); $t->string('locale'); $t->boolean('default')->default(true);
        });
        Schema::create('currencies', function ($t): void {
            $t->increments('id'); $t->boolean('default')->default(true); $t->boolean('active')->default(true);
        });
        }
        $this->app['router']->aliasMiddleware('auth', \Illuminate\Auth\Middleware\Authenticate::class);
        $this->app['router']->get('/protected', fn() => new \Illuminate\Http\JsonResponse(['authenticated'=>true]))
            ->middleware('auth:sanctum');
    }

    private function actor(string $email): User
    {
        return User::withoutEvents(fn() => User::create([
            'email'=>$email, 'firebase_token'=>['push-current','push-other'],
        ]));
    }

    private function requestAs(?string $bearer, array $body = []): FilterParamsRequest
    {
        $r = FilterParamsRequest::create('/api/v1/auth/logout','POST',$body);
        $r->headers->set('Accept','application/json');
        if ($bearer !== null) $r->headers->set('Authorization','Bearer '.$bearer);
        $this->app['auth']->forgetGuards();
        $this->app->instance('request',$r);
        return $r;
    }

    private function canAuthenticate(string $bearer): bool
    {
        $this->requestAs($bearer);
        return $this->app['auth']->guard('sanctum')->check();
    }

    private function protectedStatus(string $bearer): int
    {
        $this->requestAs($bearer);
        $r=Request::create('/protected','GET');
        $r->headers->set('Authorization','Bearer '.$bearer);
        $r->headers->set('Accept','application/json');
        $this->app->instance('request',$r);
        try { return $this->app['router']->dispatch($r)->getStatusCode(); }
        catch (\Illuminate\Auth\AuthenticationException) { return 401; }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pushCases')]
    public function test_native_logout_revokes_only_current_token_and_is_repeat_safe(array $body): void
    {
        $a=$this->actor('logout-a@synthetic.test'); $b=$this->actor('logout-b@synthetic.test');
        $session=$a->createToken('session'); $sibling=$a->createToken('sibling'); $other=$b->createToken('other');
        self::assertTrue($this->canAuthenticate($session->plainTextToken));
        self::assertSame(200,$this->protectedStatus($session->plainTextToken));
        $r=$this->requestAs($session->plainTextToken,$body);
        $response=(new LoginController())->logout($r);
        self::assertSame(200,$response->getStatusCode());
        self::assertFalse($this->canAuthenticate($session->plainTextToken));
        self::assertSame(401,$this->protectedStatus($session->plainTextToken));
        self::assertNull(PersonalAccessToken::findToken($session->plainTextToken));
        self::assertTrue($this->canAuthenticate($sibling->plainTextToken));
        self::assertTrue($this->canAuthenticate($other->plainTextToken));
        self::assertSame(['push-current','push-other'],$b->fresh()->firebase_token);
        self::assertSame($body ? ['push-other'] : ['push-current','push-other'],$a->fresh()->firebase_token);
        $again=(new LoginController())->logout($this->requestAs($session->plainTextToken,$body));
        self::assertSame(200,$again->getStatusCode());
        self::assertSame(2,DB::table('personal_access_tokens')->count());
        self::assertSame(200,(new LoginController())->logout($this->requestAs(null))->getStatusCode());
        self::assertSame(2,DB::table('personal_access_tokens')->count());
    }

    public static function pushCases(): array
    {
        return ['without push'=>[[]],'with push'=>[['token'=>'push-current']],
            'legacy push field'=>[['firebase_token'=>'push-current']]];
    }

    public function test_push_cleanup_failure_does_not_prevent_revocation(): void
    {
        $a=$this->actor('logout-failure@synthetic.test'); $t=$a->createToken('session');
        $r=$this->requestAs($t->plainTextToken,['token'=>'push-current']);
        User::updating(function (): void { throw new \RuntimeException('synthetic push failure'); });
        try {
            self::assertSame(200,(new LoginController())->logout($r)->getStatusCode());
            self::assertFalse($this->canAuthenticate($t->plainTextToken));
        } finally { User::flushEventListeners(); }
    }

    public function test_revocation_failure_is_not_hidden_as_success(): void
    {
        $a=$this->actor('logout-revoke-failure@synthetic.test'); $t=$a->createToken('session');
        $r=$this->requestAs($t->plainTextToken);
        PersonalAccessToken::deleting(function (): void { throw new \RuntimeException('synthetic revoke failure'); });
        try {
            $this->expectException(\RuntimeException::class);
            (new LoginController())->logout($r);
        } finally { PersonalAccessToken::flushEventListeners(); }
    }

    public function test_explicit_bearer_logout_preserves_unrelated_ambient_web_session(): void
    {
        $a=$this->actor('logout-bearer@synthetic.test'); $b=$this->actor('ambient@synthetic.test');
        $t=$a->createToken('current'); $other=$b->createToken('other');
        $r=$this->requestAs($t->plainTextToken);
        $session=$this->app['session']->driver(); $session->start();
        $session->put('unrelated-session-marker','keep');
        $r->setLaravelSession($session);
        $this->app['auth']->guard('web')->setUser($b);
        self::assertSame(200,(new LoginController())->logout($r)->getStatusCode());
        self::assertSame('keep',$session->get('unrelated-session-marker'));
        self::assertSame($b->id,$this->app['auth']->guard('web')->id());
        self::assertFalse($this->canAuthenticate($t->plainTextToken));
        self::assertTrue($this->canAuthenticate($other->plainTextToken));
    }

    public function test_cancelled_revocation_is_not_hidden_as_success(): void
    {
        $a=$this->actor('logout-cancelled@synthetic.test'); $t=$a->createToken('session');
        $r=$this->requestAs($t->plainTextToken);
        PersonalAccessToken::deleting(fn()=>false);
        try {
            $this->expectException(\RuntimeException::class);
            (new LoginController())->logout($r);
        } finally { PersonalAccessToken::flushEventListeners(); }
    }

    public function test_matching_web_session_is_invalidated_without_revoking_sibling_tokens(): void
    {
        $a=$this->actor('logout-web@synthetic.test');
        $t=$a->createToken('current'); $sibling=$a->createToken('sibling');
        $r=$this->requestAs($t->plainTextToken);
        $session=$this->app['session']->driver(); $session->start();
        $session->put('session-marker','clear');
        $oldSessionId=$session->getId();
        $r->setLaravelSession($session);
        $this->app['auth']->guard('web')->setUser($a);
        self::assertSame(200,(new LoginController())->logout($r)->getStatusCode());
        self::assertNull($session->get('session-marker'));
        self::assertNotSame($oldSessionId,$session->getId());
        self::assertFalse($this->app['auth']->guard('web')->check());
        self::assertFalse($this->canAuthenticate($t->plainTextToken));
        self::assertTrue($this->canAuthenticate($sibling->plainTextToken));
    }
}
