<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanvasElement extends Model
{
    protected $fillable = [
        'lesson_id',
        'page_number',
        'element_type',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];
}