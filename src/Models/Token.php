<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'spamdetect_tokens';

    /**
     * Disable Laravel's mass assignment protection
     */
    protected $guarded = [];

    /**
     * The tokens don't need to be timestamped.
     */
    public $timestamps = false;

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'count_ham' => 0,
        'count_spam' => 0,
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'count_ham' => 'integer',
        'count_spam' => 'integer',
    ];
}
