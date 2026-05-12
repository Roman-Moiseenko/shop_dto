<?php

namespace App\Modules\Content\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Content\Application\Actions\Public\ViewPublicPageUseCase;
use App\Modules\Content\Application\DTOs\Public\PagePublicData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use Illuminate\Http\JsonResponse;

class PublicPageController extends Controller
{
    public function __construct(
        private ViewPublicPageUseCase $useCase,
        private ContentBlockRepositoryInterface $blockRepo
    ) {}

    /**
     * Главная страница.
     */
    public function home(): JsonResponse
    {
        $page = $this->useCase->execute('home');
        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $blocks = $this->blockRepo->listByContainer(ContainerType::page(), $page->id);
        return response()->json(PagePublicData::fromEntity($page, $blocks));
    }

    /**
     * Страница по slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = $this->useCase->execute($slug);
        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $blocks = $this->blockRepo->listByContainer(ContainerType::page(), $page->id);
        return response()->json(PagePublicData::fromEntity($page, $blocks));
    }
}
