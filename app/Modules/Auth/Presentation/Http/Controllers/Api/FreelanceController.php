<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Freelance\CreateFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\IndexFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\RemoveFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\UpdateFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\ViewFreelanceUseCase;
use App\Modules\Auth\Application\Actions\User\RegisterFreelanceUserUseCase;
use App\Modules\Auth\Application\Actions\User\UpdateUserUseCase;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceCreateData;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceIndexData;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceUpdateData;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceViewData;
use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FreelanceController extends Controller
{
    public function __construct(
        private readonly FreelanceRepositoryInterface $freelanceRepository,
        private readonly CreateFreelanceUseCase       $createFreelanceUseCase,
        private readonly UpdateFreelanceUseCase       $updateFreelanceUseCase,
        private readonly RegisterFreelanceUserUseCase $registerFreelanceUserUseCase,
        private readonly UpdateUserUseCase            $updateUserUseCase,
        private readonly RemoveFreelanceUseCase       $removeFreelanceUseCase,
        private readonly ViewFreelanceUseCase         $viewFreelanceUseCase,
        private readonly IndexFreelanceUseCase        $indexFreelanceUseCase,
    )
    {
    }

    public function index(UserPermission $userPermission): JsonResponse
    {
        $freelances = $this->indexFreelanceUseCase->execute($userPermission);
        return response()->json(FreelanceIndexData::collect($freelances), Response::HTTP_CREATED);

      //  return FreelanceResource::collection($freelances)->response();
    }

    public function show(int $id, UserPermission $userPermission): JsonResponse
    {
        $freelance = $this->viewFreelanceUseCase->execute($id, $userPermission);
        return response()->json(FreelanceViewData::fromEntity($freelance), Response::HTTP_OK);
    }

    /**
     * @throws \Throwable
     */
    public function store(Request $request, UserPermission $userPermission): JsonResponse
    {
        try {
            $dto = FreelanceCreateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $freelanceDTO = $this->createFreelanceUseCase->execute($dto, $userPermission);
        return response()->json(FreelanceViewData::fromEntity($freelanceDTO), Response::HTTP_CREATED);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function update(Request $request, int $id, UserPermission $userPermission): JsonResponse
    {
        try {
            $dto = FreelanceUpdateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $freelance = $this->updateFreelanceUseCase->execute($id, $dto, $userPermission);
        return response()->json(FreelanceViewData::fromEntity($freelance));
    }

    public function destroy(int $id, UserPermission $userPermission): JsonResponse
    {
        $freelance = $this->freelanceRepository->findById($id);
        if (!$freelance) return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);

        $deleted = $this->removeFreelanceUseCase->execute($id, $userPermission);

        if (!$deleted)
            return response()->json(['message' => 'Ошибка удаления сотрудника'], Response::HTTP_NOT_MODIFIED);

        return response()->json(null, Response::HTTP_OK);
    }

    public function user(Request $request, int $id, UserPermission $userPermission): JsonResponse
    {
        $freelance = $this->freelanceRepository->findById($id);
        if (!$freelance) return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        try {
            $dto = UpdateUserData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (is_null($freelance->user)) {
            $userOut = $this->registerFreelanceUserUseCase->execute($id, $dto, $userPermission);
        } else {
            $userOut = $this->updateUserUseCase->execute($id, $dto, $userPermission);
        }
        return response()->json(UserViewData::fromEntity($userOut), Response::HTTP_OK);
    }


}
