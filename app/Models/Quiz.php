<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'created_by',
        'mode',
        'status',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'quiz_classes',
            'quiz_id',
            'class_id'
        );
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)
            ->orderBy('sort_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }
}