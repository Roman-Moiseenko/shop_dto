<?php

namespace App\Modules\Auth\Application\Actions;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity as DomainClient;
use App\Modules\Auth\Domain\Entities\UserEntity as DomainUser;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Auth\Domain\ValueObjects\Email as EmailVO;
use App\Modules\Auth\Domain\ValueObjects\Gender;
use App\Modules\Auth\Domain\ValueObjects\Address;
use App\Modules\Auth\Domain\ValueObjects\HashedPassword;
use App\Modules\Auth\Application\DTOs\ClientDTO;

use App\Modules\Auth\Infrastructure\Exceptions\UserAlreadyExistsException;
use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Auth\Infrastructure\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
class CreateClientUseCase
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(ClientDTO $dto): DomainClient
    {
        $emailVO = new EmailVO($dto->userEmail);
        if ($this->userRepository->emailExists($emailVO)) {
            throw new UserAlreadyExistsException("Email {$dto->userEmail} уже занят");
        }

        return DB::transaction(function () use ($dto, $emailVO) {
            $fullName = new FullName(implode(' ', array_filter([
                $dto->lastName,
                $dto->firstName,
                $dto->middleName,
            ])));
            $phone = new PhoneNumber($dto->phone);
            $email = $dto->email ? new EmailVO($dto->email) : null;
            $birthDate = $dto->birthDate ? new DateTimeImmutable($dto->birthDate) : null;
            $gender = $dto->gender ? new Gender($dto->gender) : null;

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

            $client = new DomainClient(
                $fullName,
                $phone,
                $email,
                $birthDate,
                $gender,
                $address,
                $dto->agreeToNewsletter,
                $dto->preferredLanguage
            );
            $client->setExternalId($dto->externalId);

            $savedClient = $this->clientRepository->save($client);

            $user = new DomainUser(
                $emailVO,
                HashedPassword::fromPlainText($dto->password)
            );
            $user->setProfile(Client::class, $savedClient->id);
            $savedUser = $this->userRepository->save($user);

            $eloquentUser = User::find($savedUser->id);
            $eloquentUser->syncRoles($dto->roleNames ?? ['client']);

            return $savedClient;
        });
    }
}
