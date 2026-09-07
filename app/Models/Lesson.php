<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CanvasElement;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'is_public',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function canvasElements()
    {
        return $this->hasMany(CanvasElement::class);
    }
}