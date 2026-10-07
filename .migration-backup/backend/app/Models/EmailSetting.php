<?php
declare(strict_types=1);

namespace App\Models;

use Database\Factories\FaqFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

/**
 * App\Models\EmailSetting
 *
 * @property int $id
 * @property boolean $smtp_auth
 * @property boolean $smtp_debug
 * @property string $host
 * @property int $port
 * @property string $password
 * @property string $from_to
 * @property string $from_site
 * @property array $ssl
 * @property boolean $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FaqTranslation|null $translation
 * @property-read Collection|FaqTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static FaqFactory factory(...$parameters)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereType($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUuid($value)
 * @mixin Eloquent
 */
class EmailSetting extends Model
{
    use HasFactory;

    const TTL = 8640000000; // 100000 day

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    public const PASSWORD_PREFIX = 'encrypted:v1:';

    public function setPasswordAttribute(mixed $value): void
    {
        // Blank/null edits cannot erase or double-encrypt the existing credential.
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return;
        }
        if (!is_string($value)) {
            throw new RuntimeException('SMTP_CREDENTIAL_INVALID');
        }
        $this->attributes['password'] = self::PASSWORD_PREFIX . Crypt::encryptString($value);
    }

    public function getPasswordAttribute(mixed $value): string
    {
        if (!is_string($value) || !str_starts_with($value, self::PASSWORD_PREFIX)) {
            // Legacy plaintext is never silently handed to a transport.
            throw new RuntimeException('SMTP_CREDENTIAL_REPLACEMENT_REQUIRED');
        }
        try {
            return Crypt::decryptString(substr($value, strlen(self::PASSWORD_PREFIX)));
        } catch (\Throwable) {
            throw new RuntimeException('SMTP_CREDENTIAL_UNAVAILABLE');
        }
    }

    public function credentialStatus(): string
    {
        $raw = $this->getRawOriginal('password') ?? ($this->attributes['password'] ?? null);
        if (!is_string($raw) || $raw === '') {
            return 'missing';
        }
        if (!str_starts_with($raw, self::PASSWORD_PREFIX)) {
            return 'replacement_required';
        }
        try {
            $this->getPasswordAttribute($raw);
            return 'configured';
        } catch (\Throwable) {
            return 'unavailable';
        }
    }

    protected $casts = [
        'ssl'        => 'array',
        'smtp_auth'  => 'bool',
        'smtp_debug' => 'bool',
        'active'     => 'bool'
    ];

    /**
     * @return mixed
     */
    public static function list(): mixed
    {
        return Cache::remember('email-settings-list', self::TTL, function () {
            return self::orderByDesc('id')->get();
        });
    }
}
