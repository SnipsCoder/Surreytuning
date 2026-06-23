<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FileOption extends Model
{
    protected $fillable = [
        'file_stage_id',
        'name',
        'description',
        'price_net',
        'vat_applicable',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_net' => 'decimal:2',
            'vat_applicable' => 'boolean',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function fileStage(): BelongsTo
    {
        return $this->belongsTo(FileStage::class);
    }

    public function fileRequests(): BelongsToMany
    {
        return $this->belongsToMany(FileRequest::class, 'file_request_options')
            ->withPivot('price_net')
            ->withTimestamps();
    }
}
