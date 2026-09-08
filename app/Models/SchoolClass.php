<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'grade',
    ];

    /**
     * このクラスに所属するユーザー
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'class_users',
            'class_id',
            'user_id'
        )->withTimestamps();
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(
            Lesson::class,
            'class_lessons',
            'class_id',
            'lesson_id'
        )->withTimestamps();
    }
}
