<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CanvasElement;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'class_lessons',
            'lesson_id',
            'class_id'
        )->withTimestamps();
    }
}