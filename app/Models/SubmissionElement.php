<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionElement extends Model
{
    protected $fillable = [
        'submission_id',
        'page_number',
        'element_type',
        'content',
    ];

    /*
     * どの回答に属するか
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(
            Submission::class
        );
    }

    /*
     * Canvas JSONを自動的に配列として扱う
     */
    protected $casts = [
        'content' => 'array',
    ];
}