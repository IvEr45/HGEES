<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $fillable = [
        'title',
        'description',
        'coordinates',
        'stroke_color',
        'stroke_opacity',
        'stroke_weight'
    ];

    protected $casts = [
        'coordinates' => 'array',
        'stroke_opacity' => 'decimal:2',
    ];
}

