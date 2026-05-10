<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Staff\CreateStaffUseCase;
use App\Modules\Auth\Application\Actions\Staff\IndexStaffUseCase;
use App\Modules\Auth\Application\Actions\Staff\RemoveStaffUseCase;
use App\Modules\Auth\Application\Actions\Staff\UpdateStaffUseCase;
use App\Modules\Auth\Application\Actions\Staff\ViewStaffUseCase;
use App\Modules\Auth\Application\Actions\User\RegisterStaffUserUseCase;
use App\Modules\Auth\Application\Actions\User\UpdateUserUseCase;
use App\Modules\Auth\Application\DTOs\Staff\StaffCreateData;
use App\Modules\Auth\Application\DTOs\Staff\StaffIndexData;
use App\Modules\Auth\Application\DTOs\Staff\StaffUpdateData;
use App\Modules\Auth\Application\DTOs\Staff\StaffViewData;
use App\Modules\Auth\Application\DTOs\User\UpdateUserData;
use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffRepositoryInterface $staffRepository,
        private readonly CreateStaffUseCase       $createStaffUseCase,
        private readonly UpdateStaffUseCase       $updateStaffUseCase,
        private readonly RegisterStaffUserUseCase $registerStaffUserUseCase,
        private readonly UpdateUserUseCase        $updateUserUseCase,
        private readonly RemoveStaffUseCase       $removeStaffUseCase,
        private readonly IndexStaffUseCase        $indexStaffUseCase,
        private readonly ViewStaffUseCase         $viewStaffUseCase,
    )
    {
    }

    public function index(UserPermission $userPermission): JsonResponse
    {
        $staffs = $this->indexStaffUseCase->execute($userPermission);

        return response()->json(StaffIndexData::collect($staffs), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $userPermission): JsonResponse
    {
        $staff = $this->viewStaffUseCase->execute($id, $userPermission);
        return response()->json(StaffViewData::fromEntity($staff), Response::HTTP_CREATED);
    }

    /**
     * @throws \Throwable
     */
    public function store(Request $request, UserPermission $userPermission): JsonResponse
    {
        try {
            $dto = StaffCreateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $staffDTO = $this->createStaffUseCase->execute($dto, $userPermission);
        return response()->json(StaffViewData::fromEntity($staffDTO), Response::HTTP_CREATED);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function update(Request $request, int $id, UserPermission $userPermission): JsonResponse
    {
        try {
            $dto = StaffUpdateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $staff = $this->updateStaffUseCase->execute($id, $dto, $userPermission);
        return response()->json(StaffViewData::fromEntity($staff));
    }

    public function destroy(int $id, UserPermission $userPermission): JsonResponse
    {
        $staff = $this->staffRepository->findById($id);
        if (!$staff) return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);

        $deleted = $this->removeStaffUseCase->execute($id, $userPermission);
        if (!$deleted)
            return response()->json(['message' => 'Ошибка удаления сотрудника'], Response::HTTP_NOT_MODIFIED);

        return response()->json(null, Response::HTTP_OK);
    }

    public function user(Request $request, int $id, UserPermission $userPermission): JsonResponse
    {
        $staff = $this->staffRepository->findById($id);
        if (!$staff) return response()->json(['message' => 'Сотрудник не найден'], Response::HTTP_NOT_FOUND);
        try {
            $dto = UpdateUserData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (is_null($staff->user)) {
            $userOut = $this->registerStaffUserUseCase->execute($id, $dto, $userPermission);
        } else {
            $userOut = $this->updateUserUseCase->execute($id, $dto, $userPermission);
        }
        return response()->json(UserViewData::fromEntity($userOut), Response::HTTP_OK);
    }

}
