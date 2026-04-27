<?php

namespace App\Modules\Auth\Application\Actions\Freelance;

use App\Modules\Auth\Application\DTOs\Freelance\FreelanceCreateData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use App\Modules\Auth\Domain\ValueObjects\FullName;


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
    public function execute(FreelanceCreateData $dto): FreelanceEntity
    {

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
