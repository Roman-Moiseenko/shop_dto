<?php

namespace App\Modules\Auth\Application\Actions;
use App\Modules\Auth\Application\DTOs\Client\ClientUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\Email as EmailVO;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use DateTimeImmutable;
use InvalidArgumentException;

class UpdateClientUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function execute(int $clientId, ClientUserData $dto): void
    {
        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new InvalidArgumentException('Клиент не найден');
        }

        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));
        $client->fullName = $fullName;
        $client->phone = new PhoneNumber($dto->phone);
        $client->email = $dto->email ? new EmailVO($dto->email) : null;
        $client->birthDate = $dto->birthDate ? new DateTimeImmutable($dto->birthDate) : null;
        $client->gender = $dto->gender ? new Gender($dto->gender) : null;

        $address = null;
        if ($dto->country && $dto->city && $dto->street) {
            $address = new Address(
                $dto->country,
                $dto->city,
                $dto->street,
                $dto->region,
                $dto->postalCode
            );
        }
        $client->address = $address;
        $client->agreeToNewsletter = $dto->agreeToNewsletter;
        $client->preferredLanguage = $dto->preferredLanguage;
        $client->externalId = $dto->externalId;

        $this->clientRepository->save($client);
    }
}
