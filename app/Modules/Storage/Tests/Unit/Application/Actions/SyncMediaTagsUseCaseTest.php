<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;

use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\SyncMediaTagsUseCase;
use App\Modules\Storage\Application\DTOs\SyncMediaTagsData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class SyncMediaTagsUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaRepositoryInterface $mediaRepo;
    private SyncMediaTagsUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new SyncMediaTagsUseCase($this->mediaRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function syncs_tags_successfully_when_media_exists_and_permission_granted(): void
    {
        $mediaId = 42;
        $tagIds = [1, 2, 3];
        $dto = new SyncMediaTagsData(tagIds: $tagIds);
        $existingMedia = Mockery::mock(MediaEntity::class);

        $this->mediaRepo->shouldReceive('findById')
            ->with($mediaId)
            ->once()
            ->andReturn($existingMedia);

        $this->mediaRepo->shouldReceive('syncTags')
            ->with($mediaId, $tagIds)
            ->once()
            ->andReturnNull();

        $this->useCase->execute($mediaId, $dto, $this->mockUserPermission(edit: true));
        // Если исключений нет – тест пройден
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $mediaId = 42;
        $tagIds = [1];
        $dto = new SyncMediaTagsData(tagIds: $tagIds);

        $this->mediaRepo->shouldNotReceive('findById');
        $this->mediaRepo->shouldNotReceive('syncTags');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($mediaId, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_media_not_found_when_media_does_not_exist(): void
    {
        $mediaId = 999;
        $dto = new SyncMediaTagsData(tagIds: [1]);

        $this->mediaRepo->shouldReceive('findById')
            ->with($mediaId)
            ->once()
            ->andReturn(null);

        $this->expectException(MediaFileNotFoundException::class);
        $this->useCase->execute($mediaId, $dto, $this->mockUserPermission(edit: true));
    }
}
