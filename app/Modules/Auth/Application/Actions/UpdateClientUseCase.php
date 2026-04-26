<?php

namespace App\Modules\Auth\Application\Actions;
use App\Modules\Auth\Application\DTOs\ClientDTO;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email as EmailVO;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\Address;
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
    public function execute(int $clientId, ClientDTO $dto): void
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
        $client->setFullName($fullName);
        $client->setPhone(new PhoneNumber($dto->phone));
        $client->setEmail($dto->email ? new EmailVO($dto->email) : null);
        $client->setBirthDate($dto->birthDate ? new DateTimeImmutable($dto->birthDate) : null);
        $client->setGender($dto->gender ? new Gender($dto->gender) : null);

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
        $client->setAddress($address);
        $client->setAgreeToNewsletter($dto->agreeToNewsletter);
        $client->setPreferredLanguage($dto->preferredLanguage);
        $client->setExternalId($dto->externalId);

        $this->clientRepository->save($client);
    }
}
