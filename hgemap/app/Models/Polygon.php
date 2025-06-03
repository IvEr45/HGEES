<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polygon extends Model
{
    protected $fillable = [
        'title', 
        'description', 
        'coordinates',
        'stroke_color',
        'stroke_opacity',
        'stroke_weight',
        'fill_color',
        'fill_opacity'
    ];

    protected $casts = [
        'coordinates' => 'array',
        'stroke_opacity' => 'decimal:2',
        'fill_opacity' => 'decimal:2',
    ];
}