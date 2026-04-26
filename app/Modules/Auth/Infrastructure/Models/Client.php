<?php

namespace App\Modules\Auth\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'phone', 'email',
        'birth_date', 'gender', 'country', 'region', 'city', 'street',
        'postal_code', 'is_active', 'agree_to_newsletter',
        'preferred_language', 'external_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'agree_to_newsletter' => 'boolean',
    ];

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profileable');
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]));
    }
}
