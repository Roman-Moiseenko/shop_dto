<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\SettingSite\Footer\GetFooterSettingsUseCase;
use App\Modules\Content\Application\Actions\SettingSite\Footer\SaveFooterSettingsUseCase;
use App\Modules\Content\Application\Actions\SettingSite\Header\GetHeaderSettingsUseCase;
use App\Modules\Content\Application\Actions\SettingSite\Header\SaveHeaderSettingsUseCase;
use App\Modules\Content\Application\DTOs\SettingSite\Footer\FooterSettingsSaveData;
use App\Modules\Content\Application\DTOs\SettingSite\Header\HeaderSettingsSaveData;
use App\Modules\Shared\Domain\Entities\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly SaveHeaderSettingsUseCase $saveHeaderUseCase,
        private readonly SaveFooterSettingsUseCase $saveFooterUseCase,
        private readonly GetHeaderSettingsUseCase $getHeaderUseCase,
        private readonly GetFooterSettingsUseCase $getFooterUseCase,
    ) {}

    public function getHeader(UserPermission $permissions): JsonResponse
    {
        return response()->json($this->getHeaderUseCase->execute($permissions));
    }

    public function updateHeader(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = HeaderSettingsSaveData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->saveHeaderUseCase->execute($dto, $permissions);
        return response()->json(['message' => 'Header updated']);
    }

    public function getFooter(UserPermission $permissions): JsonResponse
    {
        return response()->json($this->getFooterUseCase->execute($permissions));
    }

    public function updateFooter(Request $request, UserPermission $permissions): JsonResponse
    {
        try {
            $dto = FooterSettingsSaveData::validateAndCreate($request);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->saveFooterUseCase->execute($dto, $permissions);
        return response()->json(['message' => 'Footer updated']);
    }
}
