<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\DTOs\Freelance\FreelanceCreateData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;


readonly class CreateFreelanceUseCase
{
    public function __construct(
        private FreelanceRepositoryInterface $staffRepository
    )
    {
    }

    /**
     * @throws \Throwable
     */
    public function execute(FreelanceCreateData $dto, UserPermission $permissions): FreelanceEntity
    {
        if (!$permissions->can('auth.employee.create')) throw new AccessDeniedException();

        $fullName = new FullName(implode(' ', array_filter([
            $dto->lastName,
            $dto->firstName,
            $dto->middleName,
        ])));


        $staff = new FreelanceEntity(
            $fullName,
            $dto->position,
        );

        return $this->staffRepository->save($staff);
    }

}
