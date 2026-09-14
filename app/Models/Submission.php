<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'lesson_id',
        'student_id',
        'status',
        'submitted_at',
    ];

    /*
     * どの教材への回答か
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(
            Lesson::class
        );
    }

    /*
     * 誰が回答したか
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'student_id'
        );
    }

    /*
     * 生徒のCanvasデータ
     */
    public function elements(): HasMany
    {
        return $this->hasMany(
            SubmissionElement::class
        );
    }
}