<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Contact\ActivateContactUseCase;
use App\Modules\Content\Application\Actions\Contact\CreateContactUseCase;
use App\Modules\Content\Application\Actions\Contact\DeactivateContactUseCase;
use App\Modules\Content\Application\Actions\Contact\DeleteContactUseCase;
use App\Modules\Content\Application\Actions\Contact\IndexContactsUseCase;
use App\Modules\Content\Application\Actions\Contact\ReorderContactUseCase;
use App\Modules\Content\Application\Actions\Contact\UpdateContactUseCase;
use App\Modules\Content\Application\Actions\Contact\ViewContactUseCase;
use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\DTOs\Contact\ContactIndexData;
use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use App\Modules\Content\Application\DTOs\Contact\ReorderContactData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function __construct(
        private readonly CreateContactUseCase      $createUseCase,
        private readonly UpdateContactUseCase      $updateUseCase,
        private readonly DeleteContactUseCase      $deleteUseCase,
        private readonly IndexContactsUseCase      $indexUseCase,
        private readonly ViewContactUseCase        $viewUseCase,
        private readonly ActivateContactUseCase    $activateUseCase,
        private readonly DeactivateContactUseCase  $deactivateUseCase,
        private readonly ReorderContactUseCase     $reorderUseCase,
    ) {}

    public function index(UserPermission $permissions): JsonResponse
    {
        $contacts = $this->indexUseCase->execute($permissions);
        return response()->json(ContactIndexData::collect($contacts));
    }

    public function store(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ContactData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $contact = $this->createUseCase->execute($dto, $permissions);
        return response()->json(ContactViewData::fromEntity($contact), Response::HTTP_CREATED);
    }

    public function show(int $id, UserPermission $permissions): JsonResponse
    {
        $contact = $this->viewUseCase->execute($id, $permissions);
        return response()->json(ContactViewData::fromEntity($contact));
    }

    public function update(Request $request, int $id, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ContactData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $contact = $this->updateUseCase->execute($id, $dto, $permissions);
        return response()->json(ContactViewData::fromEntity($contact));
    }

    public function destroy(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deleteUseCase->execute($id, $permissions);
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function activate(int $id, UserPermission $permissions): JsonResponse
    {
        $this->activateUseCase->execute($id, $permissions);
        return response()->json(['message' => 'Контакт активирован']);
    }

    public function deactivate(int $id, UserPermission $permissions): JsonResponse
    {
        $this->deactivateUseCase->execute($id, $permissions);
        return response()->json(['message' => 'Контакт деактивирован']);
    }

    public function reorder(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ReorderContactData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->reorderUseCase->execute($dto, $permissions);
        return response()->json(['message' => 'Сортировка обновлена']);
    }
}
