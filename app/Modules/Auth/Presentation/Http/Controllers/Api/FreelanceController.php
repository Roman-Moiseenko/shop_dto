<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Freelance\CreateFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\RemoveFreelanceUseCase;
use App\Modules\Auth\Application\Actions\Freelance\UpdateFreelanceUseCase;
use App\Modules\Auth\Application\Actions\User\RegisterFreelanceUserUseCase;
use App\Modules\Auth\Application\Actions\User\UpdateUserUseCase;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceCreateData;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceUpdateData;
use App\Modules\Auth\Application\DTOs\Freelance\FreelanceUserData;
use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\DTOs\User\UserData;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Freelance;
use App\Modules\Auth\Presentation\Http\Resources\FreelanceResource;
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
    )
    {
    }

    public function index(UserPermission $userPermission): JsonResponse
    {
        // Простейшая реализация через модель (можно через репозиторий)
        $freelance = Freelance::with('user')->paginate();
        return FreelanceResource::collection($freelance)->response();
    }

    public function show(int $id, UserPermission $userPermission): JsonResponse
    {
        $freelance = $this->freelanceRepository->findById($id);
        if (!$freelance) {
            return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(FreelanceUserData::fromEntity($freelance), Response::HTTP_OK);
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

        $freelanceDTO = $this->createFreelanceUseCase->execute($dto);
        return response()->json(FreelanceUserData::fromEntity($freelanceDTO), Response::HTTP_CREATED);
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

        $freelance = $this->updateFreelanceUseCase->execute($id, $dto);
        return response()->json(FreelanceUserData::fromEntity($freelance));
    }

    public function destroy(int $id, UserPermission $userPermission): JsonResponse
    {
        $freelance = $this->freelanceRepository->findById($id);
        if (!$freelance) return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);

        $deleted = $this->removeFreelanceUseCase->execute($id);

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
            $userOut = $this->registerFreelanceUserUseCase->execute($id, $dto);
        } else {
            $userOut = $this->updateUserUseCase->execute($id, $dto);
        }
        return response()->json(UserData::fromEntity($userOut), Response::HTTP_OK);
    }


}
