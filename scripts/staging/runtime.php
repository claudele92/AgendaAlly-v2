<?php
declare(strict_types=1);
// Isolated production-mode runtime. No owned dotenv, SQLite or test transport.
function stagingApplication(): Illuminate\Foundation\Application
{
    $root=dirname(__DIR__,2);
    $state=$root.'/.local/staging-mvp';
    $restore=getenv('AGENDAALLY_STAGING_INSTANCE')==='restore';
    if($restore)$state.='/restore';
    require_once $root.'/.migration-backup/backend/vendor/autoload.php';
    require_once __DIR__.'/RecoveryProbe.php';
    $app=require $root.'/.migration-backup/backend/bootstrap/app.php';
    $app->useEnvironmentPath($state);
    $app->loadEnvironmentFrom('no-environment-file');
    $app->useStoragePath($state.'/storage');
    $app->useBootstrapPath($state.'/bootstrap');
    if (!$restore) {
        $app->instance('agendaally.isolated_admin_smtp_runtime', realpath($state));
    }
    $app->beforeBootstrapping(Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        function($app)use($state,$restore):void {
            $secret=getenv('SESSION_SECRET');
            if(!is_string($secret)||strlen($secret)<16)throw new RuntimeException('Managed staging key authority unavailable');
            $key='base64:'.base64_encode(hash_hmac('sha256','AgendaAlly isolated staging APP_KEY',$secret,true));
            $password=hash_hmac('sha256','AgendaAlly isolated staging DB app',$secret);
            $app['env']='production';
            $app['config']->set([
                'app.env'=>'production','app.debug'=>false,'app.key'=>$key,'app.timezone'=>'UTC',
                'app.url'=>'https://localhost:8443','app.front_url'=>'https://localhost:8443',
                'database.default'=>'staging',
                'database.connections.staging'=>[
                    'driver'=>'mysql','host'=>'127.0.0.1','port'=>$restore?33310:33309,'database'=>'agendaally_staging_mvp',
                    'username'=>'agendaally_app','password'=>$password,'charset'=>'utf8mb4',
                    'collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true,
                    'isolation_level'=>'REPEATABLE READ','timezone'=>'+00:00',
                ],
                'development.enabled'=>false,'development.database.owned_sqlite_enabled'=>false,
                'development.database.owned_sqlite_setting_is_valid'=>true,
                'development.payments.mode'=>'disabled','development.payments.mode_is_explicit'=>true,
                'development.sms.mode'=>'disabled','development.email.mode'=>'log',
                'development.email.admin_test_enabled'=>!$restore
                    && getenv('AGENDAALLY_ISOLATED_ADMIN_SMTP_TEST')==='true',
                'development.recaptcha.enabled'=>true,'development.recaptcha.enabled_is_explicit'=>true,
                'development.firebase.enabled'=>false,'development.maps.enabled'=>false,
                'development.selected_mvp_scheduler_only'=>true,
                'development.urls.api'=>'https://localhost:8443',
                'development.urls.storefront'=>'https://localhost:8443','development.urls.admin'=>'https://localhost:8444',
                'cors.allowed_origins'=>['https://localhost:8443','https://localhost:8444'],
                'cors.supports_credentials'=>true,
                'cache.default'=>'file','cache.stores.file.path'=>$state.'/storage/framework/cache/data',
                'session.driver'=>'file','session.files'=>$state.'/storage/framework/sessions',
                'session.secure'=>true,'session.http_only'=>true,'session.same_site'=>'lax','session.domain'=>null,
                'mail.default'=>'array','queue.default'=>'database',
                'queue.connections.database.connection'=>'staging','queue.failed.database'=>'staging',
                'logging.default'=>'single','logging.channels.single.path'=>$state.'/private-logs/application.log',
                'filesystems.disks.public.root'=>$state.'/media','filesystems.disks.public.url'=>'https://localhost:8443/storage',
            ]);
        });
    return $app;
}