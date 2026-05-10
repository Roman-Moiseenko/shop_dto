<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\ContentBlocks;

use App\Modules\Content\Application\Actions\ContentBlocks\AddBlockToPageUseCase;
use App\Modules\Content\Application\DTOs\AddBlockData;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class AddBlockToPageUseCaseTest extends TestCase
{
    use MockPermission;

    private ContentBlockRepositoryInterface $blockRepo;
    private AddBlockToPageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->blockRepo = Mockery::mock(ContentBlockRepositoryInterface::class);
        $this->useCase = new AddBlockToPageUseCase($this->blockRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function adds_block_successfully_and_returns_saved_entity(): void
    {
        $pageId = 10;
        $dto = new AddBlockData(
            widgetInstanceId: 5,
            sort: 3,
            section: 'sidebar',
            caption: 'Рекламный блок'
        );

        $this->blockRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (ContentBlockEntity $block) use ($pageId, $dto) {
                return $block->containerType->getValue() === 'page'
                    && $block->containerId === $pageId
                    && $block->widgetInstanceId === $dto->widgetInstanceId
                    && $block->sort === $dto->sort
                    && $block->section === $dto->section
                    && $block->caption === $dto->caption;
            }))
            ->andReturnUsing(function (ContentBlockEntity $block) {
                $block->id = 42;
                return $block;
            });

        $result = $this->useCase->execute($pageId, $dto, $this->mockUserPermission(create: true));

        $this->assertSame(42, $result->id);
        $this->assertSame('sidebar', $result->section);
        $this->assertSame('Рекламный блок', $result->caption);
    }

    #[Test]
    public function throws_access_denied_when_missing_create_permission(): void
    {
        $pageId = 10;
        $dto = new AddBlockData(widgetInstanceId: 1, sort: 0);

        $this->blockRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($pageId, $dto, $this->mockUserPermission());
    }
}
