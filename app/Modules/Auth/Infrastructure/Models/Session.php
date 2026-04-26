<?php

namespace App\Modules\Auth\Infrastructure\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'last_activity' => 'integer',
    ];

    protected $hidden = [
        'user_id',
        'payload',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
