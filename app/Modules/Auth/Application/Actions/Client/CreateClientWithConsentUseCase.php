<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\DTOs\Client\ClientCreateWithConsentData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientAlreadyExistsException;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PersonalDataConsent;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;

/**
 * Создание клиента из сайта клиентом
 * Проверка доступа не нужна
 */
readonly class CreateClientWithConsentUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    public function execute(ClientCreateWithConsentData $dto): ClientEntity
    {
        $email = new Email($dto->email);
        if ($this->clientRepository->emailExists($email)) {
            throw new ClientAlreadyExistsException("Клиент с email {$dto->email} уже существует");
        }

        $phone = $dto->phone ? new PhoneNumber($dto->phone) : null;
        if ($phone && $this->clientRepository->phoneExists($phone)) {
            throw new ClientAlreadyExistsException("Клиент с телефоном {$dto->phone} уже существует");
        }

        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));

        $client = new ClientEntity(
            fullName: $fullName,
            email: $email,
            phone: $phone,
        );

        // Устанавливаем согласие (обязательное, с actionIdentifier)
        $client->dataConsent = new PersonalDataConsent(
            policyVersion: $dto->policyVersion,
            actionIdentifier: $dto->actionIdentifier,
            active: true,
        );

        return $this->clientRepository->save($client);
    }
}
