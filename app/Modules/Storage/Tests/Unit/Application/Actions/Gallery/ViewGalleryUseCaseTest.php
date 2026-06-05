<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Gallery\ViewGalleryUseCase;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ViewGalleryUseCaseTest extends TestCase
{
    use MockPermission;

    private GalleryRepositoryInterface $galleryRepo;
    private ViewGalleryUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryRepo = Mockery::mock(GalleryRepositoryInterface::class);
        $this->useCase = new ViewGalleryUseCase($this->galleryRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createGallery(): GalleryEntity
    {
        $gallery = new GalleryEntity(
            new GalleryName('Test Gallery'),
            new Slug('test-gallery')
        );
        $gallery->id = 42;
        return $gallery;
    }

    #[Test]
    public function returns_gallery_when_found_and_view_permission_granted(): void
    {
        $gallery = $this->createGallery();

        $this->galleryRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($gallery);

        $result = $this->useCase->execute(42, $this->mockUserPermission(view: true));
        $this->assertSame($gallery, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->galleryRepo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission()); // view: false
    }

    #[Test]
    public function throws_exception_when_gallery_not_found(): void
    {
        $this->galleryRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Галерея не найдена');
        $this->useCase->execute(999, $this->mockUserPermission(view: true));
    }
}
