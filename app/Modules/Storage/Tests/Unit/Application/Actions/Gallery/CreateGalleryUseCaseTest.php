<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Gallery\CreateGalleryUseCase;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryData;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateGalleryUseCaseTest extends TestCase
{
    use MockPermission;

    private GalleryRepositoryInterface $galleryRepo;
    private CreateGalleryUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryRepo = Mockery::mock(GalleryRepositoryInterface::class);
        $this->useCase = new CreateGalleryUseCase($this->galleryRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_gallery_with_explicit_slug(): void
    {
        $dto = new GalleryData(
            name: 'Летние скидки',
            slug: 'summer-sale',
            description: 'Акции лета',
            isActive: true
        );

        $this->galleryRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (GalleryEntity $gallery) {
                return $gallery->name->getValue() === 'Летние скидки'
                    && (string)$gallery->slug === 'summer-sale'
                    && $gallery->description === 'Акции лета'
                    && $gallery->isActive === true;
            }))
            ->andReturnUsing(function (GalleryEntity $gallery) {
                $gallery->id = 42;
                return $gallery;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertEquals(42, $result->id);
        $this->assertSame('Летние скидки', $result->name->getValue());
        $this->assertSame('summer-sale', (string)$result->slug);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function creates_gallery_with_auto_slug_from_name(): void
    {
        $dto = new GalleryData(
            name: 'Зимние новинки',
            slug: null,
            description: null,
            isActive: null
        );

        $this->galleryRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (GalleryEntity $gallery) {
                // slug должен быть сгенерирован из name (Zimnie novinki после транслитерации)
                return (string)$gallery->slug === 'zimnie-novinki'
                    && $gallery->isActive === true; // по умолчанию активна
            }))
            ->andReturnUsing(function (GalleryEntity $gallery) {
                $gallery->id = 99;
                return $gallery;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertEquals(99, $result->id);
        $this->assertSame('Зимние новинки', $result->name->getValue());
        $this->assertSame('zimnie-novinki', (string)$result->slug);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function throws_access_denied_when_create_permission_missing(): void
    {
        $dto = new GalleryData(name: 'Test', slug: 'test');

        $this->galleryRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // create: false
    }
}
