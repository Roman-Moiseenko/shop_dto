<?php

namespace App\Modules\Auth\Application\Actions\Staff;

use App\Modules\Auth\Application\DTOs\Staff\StaffUpdateData;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use DateTimeImmutable;
use InvalidArgumentException;

readonly class UpdateStaffUseCase
{
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function execute(int $staffId, StaffUpdateData $dto, UserPermission $permissions): StaffEntity
    {
        if (!$permissions->can('auth.employee.edit')) throw new AccessDeniedException();

        $staff = $this->staffRepository->findById($staffId);
        if (!$staff) throw new InvalidArgumentException('Сотрудник не найден');

        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));
        $staff->fullName = $fullName;
        $staff->position = $dto->position;
        $staff->department = $dto->department;
        $staff->workPhone = $dto->workPhone ? new PhoneNumber($dto->workPhone) : null;
        $staff->personalPhone = $dto->personalPhone ? new PhoneNumber($dto->personalPhone) : null;
        $staff->workEmail = $dto->workEmail ? new Email($dto->workEmail) : null;
        $staff->hireDate = $dto->hireDate ? new DateTimeImmutable($dto->hireDate) : null;
        $staff->birthDate = $dto->birthDate ? new DateTimeImmutable($dto->birthDate) : null;
        $staff->telegramChatId = $dto->telegramChatId;
        $staff->maxChatId = $dto->maxChatId;
        $staff->notes = $dto->notes;
        if ($dto->terminated) {
            $staff->terminate(new DateTimeImmutable());
        } else {
            $staff->rehire();
        }

        $this->staffRepository->save($staff);

        return $staff;
    }
}
