<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassUser extends Model
{
    protected $table = 'class_users';

    protected $fillable = [
        'class_id',
        'user_id',
    ];

    /**
     * 所属しているクラス
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id'
        );
    }

    /**
     * 所属しているユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
