<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\Actions\ContentBlocks\UpdateBlockCaptionUseCase;
use App\Modules\Content\Application\DTOs\ContentBlocks\UpdateBlockCaptionData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Content\Infrastructure\Exceptions\ContentBlockNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateBlockCaptionUseCaseTest extends TestCase
{
    use MockPermission;

    private ContentBlockRepositoryInterface $blockRepo;
    private UpdateBlockCaptionUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockRepo = Mockery::mock(ContentBlockRepositoryInterface::class);
        $this->useCase = new UpdateBlockCaptionUseCase($this->blockRepo);
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
            'Old caption'
        );
        $block->id = $blockId;
        return $block;
    }

    #[Test]
    public function updates_caption_successfully(): void
    {
        $pageId = 10;
        $blockId = 5;
        $block = $this->createBlock($blockId, $pageId);
        // DTO теперь содержит id блока и новый caption
        $dto = new UpdateBlockCaptionData(id: $blockId, caption: 'New caption');

        $this->blockRepo->shouldReceive('findById')
            ->with($blockId)
            ->once()
            ->andReturn($block);

        $this->blockRepo->shouldReceive('save')
            ->once()
            ->with($block)
            ->andReturn($block);

        $result = $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New caption', $result->caption);
    }

    #[Test]
    public function throws_access_denied_when_missing_edit_permission(): void
    {
        $dto = new UpdateBlockCaptionData(id: 5, caption: 'New caption');
        $this->blockRepo->shouldNotReceive('findById');
        $this->blockRepo->shouldNotReceive('save');

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

        $dto = new UpdateBlockCaptionData(id: $blockId, caption: 'New caption');
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

        $dto = new UpdateBlockCaptionData(id: $blockId, caption: 'New caption');
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

        $dto = new UpdateBlockCaptionData(id: $blockId, caption: 'New caption');
        $this->expectException(ContentBlockNotFoundException::class);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission(edit: true));
    }
}
