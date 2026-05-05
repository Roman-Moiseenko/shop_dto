<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Entities\UserEntity;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Domain\ValueObjects\PersonalDataConsent;
use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Infrastructure\Models\User;
use DateTimeImmutable;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository implements ClientRepositoryInterface
{
    use HydratesUserEntity;

    public function save(ClientEntity $client): ClientEntity
    {
        $model = $client->id
            ? Client::find($client->id)
            : new Client();

        $fullName = $client->fullName;
        $model->last_name = $fullName->getLastName();
        $model->first_name = $fullName->getFirstName();
        $model->middle_name = $fullName->getMiddleName();

        $model->email = (string) $client->email;
        $model->phone = $client->phone ? (string) $client->phone : null;

        $model->birth_date = $client->birthDate;
        $model->gender = $client->gender?->getValue();

        $address = $client->address;
        if ($address) {
            $model->country = $address->country;
            $model->region = $address->region;
            $model->city = $address->city;
            $model->street = $address->street;
            $model->postal_code = $address->postalCode;
        } else {
            $model->country = $model->region = $model->city = $model->street = $model->postal_code = null;
        }

        $model->banned_at = $client->bannedAt;

        $consent = $client->dataConsent;
        if ($consent !== null) {
            $model->consented = true;
            $model->consented_at = $consent->consentedAt;
            $model->policy_version = $consent->policyVersion;
            $model->action_identifier = $consent->actionIdentifier;
            $model->consent_active = $consent->active;
        } else {
            $model->consented = false;
            $model->consented_at = null;
            $model->policy_version = null;
            $model->action_identifier = null;
            $model->consent_active = false;
        }

        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?ClientEntity
    {
        $model = Client::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByPhone(PhoneNumber $phone): ?ClientEntity
    {
        $model = Client::where('phone', (string) $phone)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUserId(int $userId): ?ClientEntity
    {
        $user = User::find($userId);
        if (!$user || $user->profileable_type !== Client::class) {
            return null;
        }
        $model = Client::find($user->profileable_id);
        return $model ? $this->hydrate($model) : null;
    }

    public function emailExists(Email $email, ?int $excludeId = null): bool
    {
        $query = Client::where('email', (string) $email);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function phoneExists(PhoneNumber $phone, ?int $excludeId = null): bool
    {
        $query = Client::where('phone', (string) $phone);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function delete(int $id): bool
    {
        $model = Client::find($id);
        return $model ? $model->delete() : false;
    }
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Client::with('user')
            ->paginate($perPage)
            ->through(fn ($model) => $this->hydrate($model)); // ← применяем hydrate к каждому элементу
    }
    /**
     * @throws \DateMalformedStringException
     */
    private function hydrate(Client $model): ClientEntity
    {
        $fullName = new FullName(
            new FullName(
                implode(' ', array_filter([
                    $model->last_name,
                    $model->first_name,
                    $model->middle_name,
                ]))
            )
        );

        $client = new ClientEntity(
            fullName: $fullName,
            email: new Email($model->email),
            phone: $model->phone ? new PhoneNumber($model->phone) : null,
        );

        $client->id = $model->id;

        if ($model->birth_date) {
            $client->birthDate = DateTimeImmutable::createFromMutable($model->birth_date);
        }
        if ($model->gender) {
            $client->gender = new Gender($model->gender);
        }

        if ($model->country || $model->city || $model->region) {
            $client->address = new Address(
                $model->country,
                $model->city,
                $model->street,
                $model->region,
                $model->postal_code
            );
        }

        if ($model->banned_at) {
            $client->bannedAt = DateTimeImmutable::createFromMutable($model->banned_at);
        }

        // Восстановление согласия
        if ($model->consented && $model->policy_version) {
            $client->dataConsent = new PersonalDataConsent(
                policyVersion: $model->policy_version,
                actionIdentifier: $model->action_identifier,
                active: $model->consent_active
            );

            if ($model->consented_at)
                $client->dataConsent->consentedAt = DateTimeImmutable::createFromMutable($model->consented_at);

        } else {
            $client->dataConsent = null;
        }

        $client->user = $this->hydrateUser($model->user);

        return $client;
    }
}
