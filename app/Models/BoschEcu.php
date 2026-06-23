<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoschEcu extends Model
{
    protected $fillable = [
        'manufacturer_number',
        'model',
        'car_producer',
        'image_path',
    ];
}
