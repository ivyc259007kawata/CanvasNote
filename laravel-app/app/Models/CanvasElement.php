<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanvasElement extends Model
{
    protected $fillable = [
        'lesson_id',
        'element_type',
        'x',
        'y',
        'width',
        'height',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];
}