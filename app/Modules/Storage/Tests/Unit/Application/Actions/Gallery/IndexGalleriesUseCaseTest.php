<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Gallery\IndexGalleriesUseCase;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexGalleriesUseCaseTest extends TestCase
{
    use MockPermission;

    private GalleryRepositoryInterface $galleryRepo;
    private IndexGalleriesUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryRepo = Mockery::mock(GalleryRepositoryInterface::class);
        $this->useCase = new IndexGalleriesUseCase($this->galleryRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_all_galleries_when_view_permission_granted(): void
    {
        $galleries = [
            new GalleryEntity(new GalleryName('Gallery 1'), new Slug('gallery-1')),
            new GalleryEntity(new GalleryName('Gallery 2'), new Slug('gallery-2')),
        ];

        $this->galleryRepo->shouldReceive('all')
            ->once()
            ->andReturn($galleries);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));
        $this->assertSame($galleries, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->galleryRepo->shouldNotReceive('all');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
