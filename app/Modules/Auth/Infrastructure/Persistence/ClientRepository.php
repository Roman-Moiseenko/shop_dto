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
        $model = $client->getId()
            ? EloquentClient::find($client->getId())
            : new EloquentClient();

        $fullName = $client->getFullName();
        $model->last_name = $fullName->getLastName();
        $model->first_name = $fullName->getFirstName();
        $model->middle_name = $fullName->getMiddleName();
        $model->phone = (string) $client->getPhone();
        $model->email = (string) $client->getEmail();
        $model->birth_date = $client->getBirthDate();
        $model->gender = $client->getGender()?->getValue();
        $model->is_active = $client->isActive();
        $model->agree_to_newsletter = $client->getAgreeToNewsletter();
        $model->preferred_language = $client->getPreferredLanguage();
        $model->external_id = $client->getExternalId();

        $address = $client->getAddress();
        if ($address) {
            $model->country = $address->getCountry();
            $model->region = $address->getRegion();
            $model->city = $address->getCity();
            $model->street = $address->getStreet();
            $model->postal_code = $address->getPostalCode();
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
        $client->setExternalId($model->external_id);
        if (!$model->is_active) {
            $client->deactivate();
        }
        return $client;
    }
}
