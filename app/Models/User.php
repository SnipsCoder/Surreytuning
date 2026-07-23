<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'dealer_id',
        'is_primary_contact',
        'can_view_pricing',
        'avatar',
        'status',
        'notify_comments_email',
        'notify_file_requests_email',
        'notify_file_requests_sms',
        'whatsapp_number',
        'last_login_at',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'email_otp_code',
        'email_otp_expires_at',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'is_primary_contact' => 'boolean',
            'can_view_pricing' => 'boolean',
            'notify_comments_email' => 'boolean',
            'notify_file_requests_email' => 'boolean',
            'notify_file_requests_sms' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Whether this user has a Google Authenticator (TOTP) secret set up.
     */
    public function hasAuthenticator(): bool
    {
        return $this->two_factor_method === 'totp' && filled($this->two_factor_secret);
    }

    /**
     * Verify a Google Authenticator (TOTP) code against this user's secret.
     * Used for step-up authentication on sensitive areas (e.g. payment keys).
     */
    public function verifyTotpCode(string $code): bool
    {
        if (! $this->hasAuthenticator()) {
            return false;
        }

        try {
            $secret = decrypt($this->two_factor_secret);
        } catch (\Throwable) {
            return false;
        }

        return (bool) (new \PragmaRX\Google2FA\Google2FA)->verifyKey($secret, $code);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function submittedFileRequests(): HasMany
    {
        return $this->hasMany(FileRequest::class, 'submitted_by_user_id');
    }

    public function fileRequestMessages(): HasMany
    {
        return $this->hasMany(FileRequestMessage::class, 'sender_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeOwnerTeam($query)
    {
        return $query->whereIn('role', [UserRole::Owner, UserRole::Tuner]);
    }

    public function scopeClients($query)
    {
        return $query->whereIn('role', [UserRole::DealerOwner, UserRole::DealerUser]);
    }

    public function updateLastLogin(): void
    {
        $this->forceFill(['last_login_at' => now()])->save();
    }
}
