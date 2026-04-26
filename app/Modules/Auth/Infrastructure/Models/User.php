<?php

namespace App\Modules\Auth\Infrastructure\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $profileable_type
 * @property int $profileable_id
 * @property string $remember_token
 * @property Carbon $email_verified_at
 * @property Carbon $banned_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'api';
    protected $fillable = [
        'email',
        'password',
        'profileable_type',
        'profileable_id',
        'banned_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'banned_at' => 'datetime',
    ];

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }
}
