<?php

namespace App\Models;

use App\Enums\UserRole;
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
            'status' => 'string',
            'is_primary_contact' => 'boolean',
            'can_view_pricing' => 'boolean',
            'notify_comments_email' => 'boolean',
            'notify_file_requests_email' => 'boolean',
            'notify_file_requests_sms' => 'boolean',
            'last_login_at' => 'datetime',
        ];
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
        return $query->whereIn('role', [UserRole::Owner, UserRole::Technician]);
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
