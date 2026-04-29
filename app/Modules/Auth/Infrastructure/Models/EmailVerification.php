<?php

namespace App\Modules\Auth\Infrastructure\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $new_email
 * @property string $token
 * @property Carbon $expires_at
 */
class EmailVerification extends Model
{
    protected $table = 'email_verifications';
    protected $fillable = ['user_id', 'new_email', 'token', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];
}
