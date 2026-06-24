<?php

namespace App\Models;

use App\Enums\FileRequestStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'dealer_id',
        'submitted_by_user_id',
        'assigned_technician_id',
        'file_type',
        'status',
        'registration',
        'vin_number',
        'make',
        'model',
        'engine',
        'engine_code',
        'year',
        'fuel',
        'euro_status',
        'transmission',
        'bhp_before',
        'torque_before_nm',
        'ecu_model_no',
        'file_stage_id',
        'tool_id',
        'client_notes',
        'price_net',
        'vat_amount',
        'price_gross',
        'is_charged',
        'client_downloaded_at',
        'void_reason',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FileRequestStatus::class,
            'fuel' => FuelType::class,
            'transmission' => TransmissionType::class,
            'price_net' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'price_gross' => 'decimal:2',
            'is_charged' => 'boolean',
            'client_downloaded_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function fileStage(): BelongsTo
    {
        return $this->belongsTo(FileStage::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(TuningTool::class, 'tool_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(FileRequestMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FileRequestAttachment::class);
    }

    public function fileRequestOptions(): HasMany
    {
        return $this->hasMany(FileRequestOption::class);
    }

    public function dtcCodes(): HasMany
    {
        return $this->hasMany(DtcCode::class);
    }

    public function getRequestNumberFormattedAttribute(): string
    {
        return 'STS-'.$this->created_at->format('Y').'-'.str_pad((string) $this->request_number, 6, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [FileRequestStatus::Closed, FileRequestStatus::Void]);
    }

    public function scopeArchived($query, int $days = 90)
    {
        return $query
            ->whereIn('status', [FileRequestStatus::Closed, FileRequestStatus::Void])
            ->where('closed_at', '<=', now()->subDays($days));
    }
}
