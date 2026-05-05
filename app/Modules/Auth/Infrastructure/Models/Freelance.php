<?php

namespace App\Modules\Auth\Infrastructure\Models;

use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Auth\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $position
 * @property string $fullName
 * @property string $notes
 * @property string $telegram_chat_id
 * @property string $max_chat_id
 * @property PhoneNumber $personal_phone
 * @property Email $personal_email
 * @property DateTime $hire_date
 * @property DateTime $termination_date
 * @property ?User $user
 */
class Freelance extends Model
{

    protected $table = 'freelances';
    protected $fillable = [
        'last_name', 'first_name', 'middle_name', 'position',
        'personal_phone', 'personal_email', 'hire_date',
        'termination_date', 'is_active',
        'telegram_chat_id', 'max_chat_id', 'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    /**
     * Полиморфная связь с User.
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profileable');
    }

    /**
     * Аксессор для полного имени (удобство).
     */
    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]));
    }
}
