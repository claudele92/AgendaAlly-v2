<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Resources\EmailTemplateResource;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailSettingService\EmailSendService;
use App\Services\EmailTemplateService\EmailTemplateService;
use App\Support\EmailTemplateContent;
use App\Support\ManagedEmailPresentation;
use App\Support\SystemEmailTemplates;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

final class TemplateCaptureMailer extends PHPMailer
{
    public bool $captured = false;
    public function send(): bool { $this->captured = true; return true; }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AdminEmailTemplateManagementTest extends IsolatedTestCase
{
    private string $compiled;
    private EmailTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiled = sys_get_temp_dir().'/agendaally-template-'.bin2hex(random_bytes(6));
        mkdir($this->compiled, 0700);
        $this->app->instance('files', new Filesystem);
        $this->app['config']->set('view', ['paths' => [resource_path('views')], 'compiled' => $this->compiled]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        Schema::create('settings', function (Blueprint $t): void { $t->id(); $t->string('key'); $t->text('value')->nullable(); });
        Schema::create('email_settings', function (Blueprint $t): void { $t->id(); $t->boolean('active'); $t->timestamps(); });
        Schema::create('email_templates', function (Blueprint $t): void {
            $t->id(); $t->integer('email_setting_id'); $t->string('subject');
            $t->text('body'); $t->text('alt_body'); $t->string('type');
            $t->integer('status'); $t->dateTime('send_to'); $t->timestamps();
        });
        foreach (['users', 'jobs', 'failed_jobs', 'selected_email_deliveries'] as $table) {
            Schema::create($table, fn (Blueprint $t) => $t->id());
        }
        Schema::create('galleries', function (Blueprint $t): void {
            $t->id(); $t->string('type')->nullable(); $t->string('path')->nullable();
            $t->string('loadable_type')->nullable(); $t->unsignedBigInteger('loadable_id')->nullable();
        });
        DB::table('email_settings')->insert(['id' => 1, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $this->service = new class extends EmailTemplateService {
            public function __construct() { $this->model = new EmailTemplate; $this->language = 'en'; }
        };
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->compiled);
        parent::tearDown();
    }

    private function presentation(string $type = 'verify'): array
    {
        $default = SystemEmailTemplates::definitions()[$type];
        return ['type' => $type, 'subject' => $default['subject'],
            'body' => $default['body'], 'alt_body' => $default['body']];
    }

    private function records(): array
    {
        return DB::table('email_templates')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    public function test_default_migration_is_idempotent_and_preserves_customization_and_schema(): void
    {
        $schema = DB::select("SELECT name,sql FROM sqlite_master WHERE type='table' ORDER BY name");
        $migration = require database_path('migrations/2026_10_08_010000_provision_account_email_templates.php');
        $migration->up();
        self::assertSame(['reset', 'subscribe', 'verify'], DB::table('email_templates')->orderBy('type')->pluck('type')->all());
        DB::table('email_templates')->where('type', 'verify')->update(['subject' => 'Preserved Admin customization']);
        $before = $this->records();
        $migration->up();
        self::assertSame(0, SystemEmailTemplates::ensure());
        $migration->down();
        self::assertSame($before, $this->records());
        self::assertEquals($schema, DB::select("SELECT name,sql FROM sqlite_master WHERE type='table' ORDER BY name"));
    }

    public function test_provisioning_defers_without_provider_then_creates_both_without_duplicates(): void
    {
        DB::table('email_settings')->delete();
        self::assertSame(0, SystemEmailTemplates::ensure());
        DB::table('email_settings')->insert(['id' => 1, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        self::assertSame(3, SystemEmailTemplates::ensure());
        self::assertSame(0, SystemEmailTemplates::ensure());
    }

    public function test_system_rows_are_visible_editable_and_resource_exposes_safe_contract(): void
    {
        SystemEmailTemplates::ensure();
        foreach (['verify', 'reset'] as $type) {
            $template = EmailTemplate::where('type', $type)->firstOrFail();
            $data = (new EmailTemplateResource($template))->resolve(request());
            self::assertSame($type, $data['type']);
            self::assertTrue($data['system_template']);
            self::assertTrue($data['previewable']);
            self::assertSame(['$verify_code'], $data['placeholders']);
            $edit = $this->presentation($type) + ['email_setting_id' => 1, 'send_to' => $template->send_to];
            $edit['subject'] = 'Customized '.$type;
            $edit['body'] = '<p>Custom wording: <strong>$verify_code</strong></p>';
            self::assertTrue($this->service->update($template, $edit)['status']);
            self::assertSame('Customized '.$type, $template->fresh()->subject);
        }
    }

    public function test_duplicate_create_does_not_delete_or_overwrite_customized_system_record(): void
    {
        SystemEmailTemplates::ensure();
        DB::table('email_templates')->where('type', 'verify')->update(['subject' => 'Keep me']);
        $before = $this->records();
        try {
            $this->service->create($this->presentation() + ['email_setting_id' => 1, 'send_to' => '2099-01-01']);
            self::fail('Duplicate system template accepted.');
        } catch (ValidationException) {}
        self::assertSame($before, $this->records());
    }

    public function test_system_delete_and_provider_schedule_type_reassignment_are_rejected(): void
    {
        SystemEmailTemplates::ensure();
        $template = EmailTemplate::where('type', 'verify')->firstOrFail();
        $before = $this->records();
        foreach (['provider', 'schedule', 'identity', 'delete', 'dropAll'] as $fault) {
            $data = $this->presentation() + ['email_setting_id' => 1, 'send_to' => $template->send_to];
            if ($fault === 'provider') $data['email_setting_id'] = 2;
            if ($fault === 'schedule') $data['send_to'] = '2100-01-01';
            if ($fault === 'identity') $data['type'] = 'reset';
            try {
                if ($fault === 'dropAll') $this->service->dropAll();
                elseif ($fault === 'delete') $this->service->delete([$template->id]);
                else $this->service->update($template, $data);
                self::fail('Application-controlled field accepted.');
            } catch (ValidationException) {}
            self::assertSame($before, $this->records());
        }
    }

    public function test_unknown_forbidden_or_executable_placeholders_and_html_fail_safely(): void
    {
        foreach (['$password', '$reset_token', '{{verification_code}}', '@php echo 1; @endphp',
            '<?php echo 1;', '<script>alert(1)</script>', '<p onclick="alert(1)">Unsafe</p>',
            '<a href="mailto:$verify_code@example.invalid">Support</a>',
            '<a href="https://attacker.invalid/reset">Reset</a>', '<span style="background:url(https://attacker.invalid)">Bad</span>'] as $unsafe) {
            $data = $this->presentation();
            $data['body'] .= $unsafe;
            try { EmailTemplateContent::validate($data); self::fail('Unsafe content accepted.'); }
            catch (ValidationException) {}
        }
        $data = $this->presentation(); $data['body'] = 'Missing required placeholder';
        $this->expectException(ValidationException::class);
        EmailTemplateContent::validate($data);
    }

    public function test_preview_uses_samples_and_creates_no_challenge_outbox_job_or_send(): void
    {
        SystemEmailTemplates::ensure();
        $before = $this->records();
        foreach (['verify', 'reset'] as $type) {
            $preview = ManagedEmailPresentation::preview($this->presentation($type));
            self::assertSame('synthetic_no_send', $preview['mode']);
            self::assertSame(['$verify_code' => '123456'], $preview['sample_values']);
            self::assertStringContainsString('123456', $preview['html']);
            self::assertStringContainsString('123456', $preview['text']);
            self::assertStringNotContainsString('$verify_code', $preview['html']);
        }
        foreach (['users', 'jobs', 'failed_jobs', 'selected_email_deliveries'] as $table) self::assertSame(0, DB::table($table)->count());
        self::assertSame($before, $this->records());
        self::assertFalse($this->app->bound(PHPMailer::class));
    }

    public function test_custom_subscription_content_crud_never_directly_dispatches_delivery_and_preserves_systems(): void
    {
        SystemEmailTemplates::ensure();
        $before = $this->records();
        $events = new \Illuminate\Events\Dispatcher($this->app);
        $this->app->instance('events', $events);
        $deliveries = 0;
        $events->listen(\App\Events\Mails\EmailSendByTemplate::class, function () use (&$deliveries): void { $deliveries++; });
        $data = ['type' => 'subscribe', 'subject' => 'Disposable content',
            'body' => '<p>Synthetic informational wording.</p>', 'alt_body' => 'Synthetic informational wording.',
            'email_setting_id' => 1, 'send_to' => date('Y-m-d H:i:s')];
        self::assertTrue($this->service->create($data)['status']);
        self::assertTrue($this->service->create($data)['status']);
        $custom = EmailTemplate::where('type', 'subscribe')->orderByDesc('id')->firstOrFail();
        $data['subject'] = 'Edited disposable content';
        self::assertTrue($this->service->update($custom, $data)['status']);
        self::assertSame('Edited disposable content', $custom->fresh()->subject);
        self::assertSame('synthetic_no_send', ManagedEmailPresentation::preview($data)['mode']);
        self::assertSame(0, $deliveries);
        $defaultId = $before[2]['id'];
        $this->service->delete(EmailTemplate::where('type', 'subscribe')->where('id', '!=', $defaultId)->pluck('id')->all());
        self::assertSame($before, $this->records());
        self::assertSame(0, DB::table('jobs')->count());
        self::assertSame(0, DB::table('selected_email_deliveries')->count());
    }

    public function test_real_sender_paths_use_managed_content_with_capture_only_transport_and_safe_fallback(): void
    {
        SystemEmailTemplates::ensure();
        $mail = new TemplateCaptureMailer(true);
        $sender = new class($mail) extends EmailSendService {
            public function __construct(private TemplateCaptureMailer $capture) { $this->language = 'en'; }
            protected function suppressEmailDelivery(string $operation): ?array { return null; }
            public function emailBaseAuth(?EmailSetting $setting, User $user): PHPMailer { return $this->capture; }
        };
        $user = (new User)->forceFill(['id' => 1, 'email' => 'synthetic@example.invalid']);
        foreach (['verify', 'reset'] as $type) {
            DB::table('email_templates')->where('type', $type)->update([
                'subject' => 'Managed '.$type, 'body' => '<p>Managed copy $verify_code</p>',
                'alt_body' => 'Managed alternate $verify_code',
            ]);
            $mail->captured = false;
            $result = $type === 'verify' ? $sender->sendVerify($user, '654321') : $sender->sendEmailPasswordReset($user, '654321');
            self::assertTrue($result['status']);
            self::assertTrue($mail->captured);
            self::assertSame('Managed '.$type, $mail->Subject);
            self::assertStringContainsString('Managed copy 654321', $mail->Body);
            self::assertStringContainsString('Managed alternate 654321', $mail->AltBody);
        }
        DB::table('email_templates')->delete();
        self::assertTrue($sender->sendVerify($user, '654321')['status']);
        self::assertSame('Verify your email address', $mail->Subject);
        self::assertTrue($sender->sendEmailPasswordReset($user, '654321')['status']);
        self::assertSame('Reset password', $mail->Subject);
    }

    public function test_subscription_default_preserves_customization_and_has_no_otp_or_send_side_effects(): void
    {
        SystemEmailTemplates::ensure();
        $template = EmailTemplate::where('type', 'subscribe')->firstOrFail();
        $resource = (new EmailTemplateResource($template))->resolve(request());
        self::assertTrue($resource['system_template']);
        self::assertTrue($resource['previewable']);
        self::assertFalse($resource['campaign_scheduled']);
        self::assertSame([], $resource['placeholders']);
        $data = ['type' => 'subscribe', 'subject' => 'Owner customized digest',
            'body' => '<p>Owner customized body.</p>', 'alt_body' => "Owner customized\nplain text.",
            'email_setting_id' => $template->email_setting_id, 'send_to' => $template->send_to];
        self::assertTrue($this->service->update($template, $data)['status']);
        self::assertSame(EmailTemplate::STATUS_LIBRARY_ONLY, (int) $template->fresh()->status);
        $before = $this->records();
        self::assertSame(0, SystemEmailTemplates::ensure());
        self::assertSame(0, SystemEmailTemplates::ensure());
        self::assertSame($before, $this->records());
        $preview = ManagedEmailPresentation::preview($data);
        self::assertSame([], $preview['sample_values']);
        self::assertSame($data['alt_body'], $preview['text']);
        self::assertStringContainsString('Owner customized body.', $preview['html']);
        self::assertSame(0, DB::table('jobs')->count());
        self::assertSame(0, DB::table('selected_email_deliveries')->count());
    }

    public function test_preexisting_customized_subscription_is_adopted_without_any_rewrite(): void
    {
        $this->service->create(['type' => 'subscribe', 'subject' => 'Existing customized digest',
            'body' => '<p>Existing content.</p>', 'alt_body' => 'Existing content.',
            'email_setting_id' => 1, 'send_to' => '2098-02-03 04:05:06']);
        DB::table('email_templates')->update(['status' => 1]);
        $before = (array) DB::table('email_templates')->first();
        self::assertSame(2, SystemEmailTemplates::ensure());
        self::assertSame(0, SystemEmailTemplates::ensure());
        self::assertSame($before, (array) DB::table('email_templates')->where('id', $before['id'])->first());
        self::assertSame(1, DB::table('email_templates')->where('type', 'subscribe')->count());
    }

    public function test_subscription_default_identity_schedule_provider_and_mixed_delete_are_protected(): void
    {
        SystemEmailTemplates::ensure();
        $template = EmailTemplate::where('type', 'subscribe')->firstOrFail();
        $this->service->create(['type' => 'subscribe', 'subject' => 'Custom removable',
            'body' => '<p>Custom content.</p>', 'alt_body' => 'Custom content.']);
        $custom = EmailTemplate::where('type', 'subscribe')->orderByDesc('id')->firstOrFail();
        $before = $this->records();
        foreach (['provider', 'schedule', 'identity', 'delete', 'mixedDelete'] as $fault) {
            $data = ['type' => 'subscribe', 'subject' => $template->subject,
                'body' => $template->body, 'alt_body' => $template->alt_body,
                'email_setting_id' => 1, 'send_to' => $template->send_to];
            if ($fault === 'provider') $data['email_setting_id'] = 2;
            if ($fault === 'schedule') $data['send_to'] = '2100-01-01';
            if ($fault === 'identity') $data['type'] = 'order';
            try {
                if ($fault === 'delete') $this->service->delete([$template->id]);
                elseif ($fault === 'mixedDelete') $this->service->delete([$template->id, $custom->id]);
                else $this->service->update($template, $data);
                self::fail('System identity changed or removed.');
            } catch (ValidationException) {}
            self::assertSame($before, $this->records());
        }
        $this->service->delete([$custom->id]);
        self::assertSame(3, EmailTemplate::count());
    }

    public function test_native_scheduler_ignores_library_content_and_retains_existing_campaign_behavior(): void
    {
        SystemEmailTemplates::ensure();
        $this->service->create(['type' => 'subscribe', 'subject' => 'Custom library content',
            'body' => '<p>Custom content.</p>', 'alt_body' => 'Custom content.']);
        DB::table('email_templates')->where('type', 'subscribe')->update(['send_to' => date('Y-m-d H')]);
        $events = new \Illuminate\Events\Dispatcher($this->app);
        $this->app->instance('events', $events);
        $sent = [];
        $events->listen(\App\Events\Mails\EmailSendByTemplate::class, function ($event) use (&$sent): void {
            $sent[] = $event->emailTemplate->id;
        });
        $before = $this->records();
        $command = new \App\Console\Commands\EmailSendByTime;
        self::assertSame(0, $command->handle());
        self::assertSame([], $sent);
        self::assertSame($before, $this->records());
        $campaign = EmailTemplate::create(['type' => 'subscribe', 'subject' => 'Existing campaign',
            'body' => 'Existing body.', 'alt_body' => 'Existing body.', 'email_setting_id' => 1,
            'send_to' => date('Y-m-d H'), 'status' => 0]);
        self::assertSame(0, $command->handle());
        self::assertSame([$campaign->id], $sent);
        self::assertSame(1, (int) $campaign->fresh()->status);
        self::assertTrue($this->service->update($campaign, [
            'type' => 'subscribe', 'subject' => 'Presentation edited',
            'body' => 'Existing body.', 'alt_body' => 'Existing body.',
            'email_setting_id' => 1, 'send_to' => $campaign->send_to])['status']);
        self::assertSame(1, (int) $campaign->fresh()->status);
    }

    public function test_existing_subscription_sender_consumes_default_with_capture_only_transport(): void
    {
        SystemEmailTemplates::ensure();
        Schema::table('users', fn (Blueprint $t) => $t->string('email'));
        Schema::create('email_subscriptions', function (Blueprint $t): void {
            $t->id(); $t->integer('user_id'); $t->boolean('active'); $t->timestamps();
        });
        // Synthetic in-memory fixture only; no native subscriber is created.
        DB::table('users')->insert(['id' => 1, 'email' => 'synthetic@example.invalid']);
        DB::table('email_subscriptions')->insert(['user_id' => 1, 'active' => 1]);
        $mail = new TemplateCaptureMailer(true);
        $sender = new class($mail) extends EmailSendService {
            public function __construct(private TemplateCaptureMailer $capture) { $this->language = 'en'; }
            protected function suppressEmailDelivery(string $operation): ?array { return null; }
            public function emailBaseAuth(?EmailSetting $setting, User $user): PHPMailer { return $this->capture; }
        };
        $template = EmailTemplate::where('type', 'subscribe')->firstOrFail();
        $before = $this->records();
        self::assertTrue($sender->sendSubscriptions($template)['status']);
        self::assertTrue($mail->captured);
        self::assertSame($template->subject, $mail->Subject);
        self::assertStringContainsString($template->body, $mail->Body);
        $preview = ManagedEmailPresentation::preview([
            'type' => 'subscribe', 'subject' => $template->subject,
            'body' => $template->body, 'alt_body' => $template->alt_body]);
        self::assertSame($mail->AltBody, $preview['text']);
        self::assertSame($before, $this->records());
        self::assertSame(0, DB::table('jobs')->count());
        self::assertSame(0, DB::table('selected_email_deliveries')->count());
    }
}
