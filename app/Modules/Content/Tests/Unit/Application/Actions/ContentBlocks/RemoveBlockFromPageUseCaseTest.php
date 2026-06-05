<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\Actions\ContentBlocks\RemoveBlockFromPageUseCase;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\Exceptions\ContentBlockNotFoundException;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class RemoveBlockFromPageUseCaseTest extends TestCase
{
    use MockPermission;

    private ContentBlockRepositoryInterface $blockRepo;
    private RemoveBlockFromPageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockRepo = Mockery::mock(ContentBlockRepositoryInterface::class);
        $this->useCase = new RemoveBlockFromPageUseCase($this->blockRepo);
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
            1, // widgetInstanceId любое
            0,
            null,
            ''
        );
        $block->id = $blockId;
        return $block;
    }

    #[Test]
    public function removes_block_successfully(): void
    {
        $pageId = 10;
        $blockId = 5;
        $block = $this->createBlock($blockId, $pageId);

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $this->blockRepo->shouldReceive('delete')
            ->with($blockId)
            ->once()
            ->andReturn(true);

        $this->useCase->execute($pageId, $blockId, $this->mockUserPermission(delete: true));
        // Если исключения нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $pageId = 10;
        $blockId = 5;

        $this->blockRepo->shouldNotReceive('findById');
        $this->blockRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($pageId, $blockId, $this->mockUserPermission());
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

        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $blockId, $this->mockUserPermission(delete: true));
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

        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $blockId, $this->mockUserPermission(delete: true));
    }

    #[Test]
    public function throws_not_found_when_block_is_not_page_type(): void
    {
        $pageId = 10;
        $blockId = 5;
        $block = $this->createBlock($blockId, $pageId, 'post'); // тип поста, не страница

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $blockId, $this->mockUserPermission(delete: true));
    }
}
