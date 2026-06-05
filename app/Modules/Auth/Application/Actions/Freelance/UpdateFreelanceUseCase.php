<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\DTOs\Freelance\FreelanceUpdateData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use DateTimeImmutable;
use InvalidArgumentException;

readonly class UpdateFreelanceUseCase
{
    public function __construct(
        private FreelanceRepositoryInterface $freelanceRepository
    ) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function execute(int $freelanceId, FreelanceUpdateData $dto, UserPermission $permissions): FreelanceEntity
    {
        if (!$permissions->can('auth.employee.edit')) throw new AccessDeniedException();
        $freelance = $this->freelanceRepository->findById($freelanceId);
        if (!$freelance) throw new InvalidArgumentException('Сотрудник не найден');

        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));
        $freelance->fullName = $fullName;
        $freelance->position = $dto->position;


        $freelance->personalPhone = $dto->personalPhone ? new PhoneNumber($dto->personalPhone) : null;
        $freelance->personalEmail = $dto->personalEmail ? new Email($dto->personalEmail) : null;
        $freelance->hireDate = $dto->hireDate ? new DateTimeImmutable($dto->hireDate) : null;
        $freelance->telegramChatId = $dto->telegramChatId;
        $freelance->maxChatId = $dto->maxChatId;
        $freelance->notes = $dto->notes;
        if ($dto->terminated) {
            $freelance->terminate(new DateTimeImmutable());
        } else {
            $freelance->rehire();
        }

        $this->freelanceRepository->save($freelance);

        return $freelance;
    }
}
