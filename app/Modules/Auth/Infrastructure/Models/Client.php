<?php

namespace App\Modules\Auth\Infrastructure\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $email
 * @property string $phone
 * @property Carbon $birth_date
 * @property string $gender
 * @property string $country
 * @property string $region
 * @property string $city
 * @property string $street
 * @property string $postal_code
 * @property Carbon $banned_at
 * @property bool $consented
 * @property Carbon $consented_at
 * @property string $policy_version
 * @property string $action_identifier
 * @property bool $consent_active
 * @property User $user
 */
class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'country',
        'region',
        'city',
        'street',
        'postal_code',
        'banned_at',
        'consented',
        'consented_at',
        'policy_version',
        'action_identifier',
        'consent_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'banned_at' => 'datetime',
        'consented' => 'boolean',
        'consented_at' => 'datetime',
        'consent_active' => 'boolean',
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
