<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\Actions\ContentBlocks\ReorderSingleBlockUseCase;
use App\Modules\Content\Application\DTOs\ContentBlocks\ReorderSingleBlockData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Exceptions\ContentBlockNotFoundException;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ReorderSingleBlockUseCaseTest extends TestCase
{
    use MockPermission;

    private ContentBlockRepositoryInterface $blockRepo;
    private ReorderSingleBlockUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockRepo = Mockery::mock(ContentBlockRepositoryInterface::class);
        $this->useCase = new ReorderSingleBlockUseCase($this->blockRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createBlock(int $blockId, int $pageId, string $containerType = 'page'): ContentBlockEntity
    {
        $block = new ContentBlockEntity(
            new ContainerType($containerType),
            $pageId,
            1,
            0,
            null,
            ''
        );
        $block->id = $blockId;
        return $block;
    }

    #[Test]
    public function reorders_block_successfully(): void
    {
        $pageId = 10;
        $blockId = 5;
        $newSort = 3;
        $block = $this->createBlock($blockId, $pageId);

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $this->blockRepo->shouldReceive('updateSortOrder')
            ->once()
            ->with($blockId, $newSort, Mockery::type(ContainerType::class), $pageId);

        $dto = new ReorderSingleBlockData(id: $blockId, sort: $newSort);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));
        // Успех – исключений нет
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $dto = new ReorderSingleBlockData(id: 5, sort: 3);
        $this->blockRepo->shouldNotReceive('findById');
        $this->blockRepo->shouldNotReceive('updateSortOrder');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_not_found_when_block_does_not_exist(): void
    {
        $pageId = 10;
        $blockId = 999;
        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn(null);

        $dto = new ReorderSingleBlockData(id: $blockId, sort: 2);
        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));
    }

    #[Test]
    public function throws_not_found_when_block_does_not_belong_to_page(): void
    {
        $pageId = 10;
        $blockId = 5;
        $block = $this->createBlock($blockId, 20); // другой containerId

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $dto = new ReorderSingleBlockData(id: $blockId, sort: 2);
        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));
    }

    #[Test]
    public function throws_not_found_when_block_container_type_is_not_page(): void
    {
        $pageId = 10;
        $blockId = 5;
        $block = $this->createBlock($blockId, $pageId, 'post');

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $dto = new ReorderSingleBlockData(id: $blockId, sort: 2);
        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));
    }
}
