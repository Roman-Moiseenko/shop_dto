<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\ContentBlocks\AddBlockToPageUseCase;
use App\Modules\Content\Application\Actions\ContentBlocks\RemoveBlockFromPageUseCase;
use App\Modules\Content\Application\Actions\ContentBlocks\ReorderSingleBlockUseCase;
use App\Modules\Content\Application\Actions\ContentBlocks\UpdateBlockCaptionUseCase;
use App\Modules\Content\Application\DTOs\AddBlockData;
use App\Modules\Content\Application\DTOs\ReorderSingleBlockData;
use App\Modules\Content\Application\DTOs\UpdateBlockCaptionData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PageBlockController extends Controller
{
    public function __construct(
        private readonly AddBlockToPageUseCase      $addBlockUseCase,
        private readonly RemoveBlockFromPageUseCase $removeBlockUseCase,
        private readonly UpdateBlockCaptionUseCase  $updateBlockCaptionUseCase,
        private readonly ReorderSingleBlockUseCase  $reorderSingleBlockUseCase,
    ) {}

    public function addBlock(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = AddBlockData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $block = $this->addBlockUseCase->execute($id, $dto, $permissions);
        return response()->json($block, 201);
    }

    public function removeBlock(int $id, int $blockId, UserPermission $permissions): JsonResponse
    {
        $this->removeBlockUseCase->execute($id, $blockId, $permissions);
        return response()->json(null, 204);
    }

    public function updateSort(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = ReorderSingleBlockData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->reorderSingleBlockUseCase->execute($id, $dto, $permissions);
        return response()->json(['message' => 'Порядок обновлён']);
    }

    public function updateCaption(int $id, Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = UpdateBlockCaptionData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $block = $this->updateBlockCaptionUseCase->execute($id, $dto, $permissions);
        return response()->json($block);
    }
}
