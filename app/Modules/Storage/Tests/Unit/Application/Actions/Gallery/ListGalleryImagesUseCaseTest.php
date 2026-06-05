<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Gallery\ListGalleryImagesUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ListGalleryImagesUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaRepositoryInterface $mediaRepo;
    private ListGalleryImagesUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new ListGalleryImagesUseCase($this->mediaRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_images_without_tags_when_no_filter(): void
    {
        $galleryId = 5;
        $filters = [];
        $images = [Mockery::mock(MediaEntity::class)];

        $this->mediaRepo->shouldReceive('listByEntityWithTags')
            ->with('storage.gallery', $galleryId, $filters)
            ->once()
            ->andReturn($images);

        $result = $this->useCase->execute($galleryId, $filters, $this->mockUserPermission(view: true));
        $this->assertSame($images, $result);
    }

    #[Test]
    public function filters_by_tags_array(): void
    {
        $galleryId = 5;
        $tags = ['sale', 'new'];
        $images = [];

        $this->mediaRepo->shouldReceive('listByEntityWithTags')
            ->with('storage.gallery', $galleryId, $tags)
            ->once()
            ->andReturn($images);

        $result = $this->useCase->execute($galleryId, $tags, $this->mockUserPermission(view: true));
        $this->assertSame($images, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->mediaRepo->shouldNotReceive('listByEntityWithTags');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(5, [], $this->mockUserPermission()); // view: false
    }
}
