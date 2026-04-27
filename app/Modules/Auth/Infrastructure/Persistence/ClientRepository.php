<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity as DomainClient;
use App\Modules\Auth\Infrastructure\Models\Client as EloquentClient;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email as EmailVO;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\Address;
use DateTimeImmutable;

class ClientRepository implements ClientRepositoryInterface
{
    public function save(DomainClient $client): DomainClient
    {
        $model = $client->id
            ? EloquentClient::find($client->id)
            : new EloquentClient();

        $fullName = $client->fullName;
        $model->last_name = $fullName->getLastName();
        $model->first_name = $fullName->getFirstName();
        $model->middle_name = $fullName->getMiddleName();
        $model->phone = (string)$client->phone;
        $model->email = (string)$client->email;
        $model->birth_date = $client->birthDate;
        $model->gender = $client->gender?->getValue();
        $model->is_active = $client->isActive;
        $model->agree_to_newsletter = $client->agreeToNewsletter;
        $model->preferred_language = $client->preferredLanguage;
        $model->external_id = $client->externalId;

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

        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?DomainClient
    {
        $model = EloquentClient::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByPhone(PhoneNumber $phone): ?DomainClient
    {
        $model = EloquentClient::where('phone', (string) $phone)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUserId(int $userId): ?DomainClient
    {
        $user = \Modules\Auth\Infrastructure\Models\User::find($userId);
        if (!$user || $user->profileable_type !== EloquentClient::class) {
            return null;
        }
        $model = EloquentClient::find($user->profileable_id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): bool
    {
        $model = EloquentClient::find($id);
        return $model ? $model->delete() : false;
    }

    private function hydrate(EloquentClient $model): DomainClient
    {
        $fullName = new FullName($model->full_name);
        $phone = new PhoneNumber($model->phone);
        $email = $model->email ? new EmailVO($model->email) : null;
        $birthDate = $model->birth_date ? DateTimeImmutable::createFromMutable($model->birth_date) : null;
        $gender = $model->gender ? new Gender($model->gender) : null;

        $address = null;
        if ($model->country && $model->city && $model->street) {
            $address = new Address(
                $model->country,
                $model->city,
                $model->street,
                $model->region,
                $model->postal_code
            );
        }

        $client = new DomainClient(
            $fullName,
            $phone,
            $email,
            $birthDate,
            $gender,
            $address,
            $model->agree_to_newsletter,
            $model->preferred_language
        );
        $client->setId($model->id);
        $client->externalId = $model->external_id;
        if (!$model->is_active) {
            $client->deactivate();
        }
        return $client;
    }
}
