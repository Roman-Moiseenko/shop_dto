<?php

namespace App\Modules\Shared\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['module', 'key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];
}
