<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DtcLibrary extends Model
{
    const CREATED_AT = null;

    const UPDATED_AT = null;

    protected $table = 'dtc_library';

    protected $fillable = [
        'code',
        'description',
    ];
}
