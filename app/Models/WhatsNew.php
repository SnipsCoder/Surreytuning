<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsNew extends Model
{
    use SoftDeletes;

    protected $table = 'whats_news';

    protected $fillable = [
        'title',
        'body',
        'version',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
        ];
    }
}
