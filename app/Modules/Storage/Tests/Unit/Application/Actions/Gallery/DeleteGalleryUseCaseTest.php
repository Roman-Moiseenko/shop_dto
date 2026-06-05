<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Gallery\DeleteGalleryUseCase;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DeleteGalleryUseCaseTest extends TestCase
{
    use MockPermission;

    private GalleryRepositoryInterface $galleryRepo;
    private DeleteGalleryUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryRepo = Mockery::mock(GalleryRepositoryInterface::class);
        $this->useCase = new DeleteGalleryUseCase($this->galleryRepo);
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
        $gallery->id = 10;
        return $gallery;
    }

    #[Test]
    public function deletes_gallery_successfully(): void
    {
        $gallery = $this->createGallery();

        $this->galleryRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($gallery);

        $this->galleryRepo->shouldReceive('delete')
            ->with(10)
            ->once();

        $this->useCase->execute(10, $this->mockUserPermission(delete: true));
        // Исключений нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $this->galleryRepo->shouldNotReceive('findById');
        $this->galleryRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // delete: false
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
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }
}
