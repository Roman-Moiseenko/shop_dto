<?php

namespace App\Modules\Auth\Application\Actions\Client;

use App\Modules\Auth\Application\DTOs\Client\ClientUpdateData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientAlreadyExistsException;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use DateTimeImmutable;
use InvalidArgumentException;

class UpdateClientUseCase
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly UserRepositoryInterface   $userRepository,
    )
    {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function execute(int $clientId, ClientUpdateData $dto, UserPermission $permissions): ClientEntity
    {
        if (!$permissions->can('auth.buyer.edit')) throw new AccessDeniedException();

        $client = $this->clientRepository->findById($clientId);
        if (!$client) {
            throw new InvalidArgumentException('Клиент не найден');
        }

        // Обновляем ФИО
        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));
        $client->fullName = $fullName;

        // Телефон
        if ($dto->phone !== null) {
            $phone = $dto->phone ? new PhoneNumber($dto->phone) : null;
            if ($phone && $this->clientRepository->phoneExists($phone, $clientId)) {
                throw new ClientAlreadyExistsException('Телефон уже используется другим клиентом');
            }
            $client->phone = $phone;
        }

        // Email
        if ($dto->email !== null) {
            $email = $dto->email ? new Email($dto->email) : null;
            if ($email) {
                // Проверяем уникальность среди клиентов
                if ($this->clientRepository->emailExists($email, $clientId)) {
                    throw new ClientAlreadyExistsException('Email уже используется другим клиентом');
                }
                // Проверяем, что такой email не занят в User
                $excludeUserId = $client->user?->id;
                if ($this->userRepository->emailExists($email, $excludeUserId)) {
                    throw new ClientAlreadyExistsException('Email уже используется пользователем системы');
                }
            }
            $client->email = $email;
        }

        // Дата рождения
        if ($dto->birthDate !== null)
            $client->birthDate = $dto->birthDate ? new DateTimeImmutable($dto->birthDate) : null;

        // Пол
        if ($dto->gender !== null)
            $client->gender = $dto->gender ? new Gender($dto->gender) : null;

        // Адрес
        if ($dto->country !== null || $dto->city !== null || $dto->region !== null) {
            $client->address = new Address(
                $dto->country ?? '',
                $dto->city ?? '',
                $dto->street ?? '',
                $dto->region ?? '',
                $dto->postalCode ?? ''
            );
        } else {
            $client->address = null;
        }


        return $this->clientRepository->save($client);
    }
}
