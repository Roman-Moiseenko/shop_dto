<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Gallery\UpdateGalleryUseCase;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryData;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateGalleryUseCaseTest extends TestCase
{
    use MockPermission;

    private GalleryRepositoryInterface $galleryRepo;
    private UpdateGalleryUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->galleryRepo = Mockery::mock(GalleryRepositoryInterface::class);
        $this->useCase = new UpdateGalleryUseCase($this->galleryRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createExistingGallery(): GalleryEntity
    {
        $gallery = new GalleryEntity(
            new GalleryName('Старое название'),
            new Slug('old-slug'),
            'Старое описание',
            true
        );
        $gallery->id = 42;
        return $gallery;
    }

    #[Test]
    public function updates_all_fields_successfully(): void
    {
        $existing = $this->createExistingGallery();

        $this->galleryRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($existing);

        $this->galleryRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (GalleryEntity $gallery) {
                return $gallery->name->getValue() === 'Новое название'
                    && (string) $gallery->slug === 'new-slug'
                    && $gallery->description === 'Новое описание'
                    && $gallery->isActive === false;
            }))
            ->andReturn($existing);

        $dto = new GalleryData(
            name: 'Новое название',
            slug: 'new-slug',
            description: 'Новое описание',
            isActive: false
        );

        $result = $this->useCase->execute(42, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('Новое название', $result->name->getValue());
        $this->assertSame('new-slug', (string) $result->slug);
        $this->assertFalse($result->isActive);
    }

    #[Test]
    public function auto_generates_slug_when_not_provided(): void
    {
        $existing = $this->createExistingGallery();

        $this->galleryRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($existing);

        $this->galleryRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(GalleryEntity::class))
            ->andReturnUsing(function (GalleryEntity $gallery) use ($existing) {
                // ожидаем автоматическую транслитерацию "Новое имя" → "novoe-imya"
                if ((string) $gallery->slug !== 'novoe-imya') {
                    throw new \PHPUnit\Framework\ExpectationFailedException('Slug was not auto-generated correctly');
                }
                return $existing;
            });

        $dto = new GalleryData(
            name: 'Новое имя',
            slug: null,
            description: null,
            isActive: null
        );

        $result = $this->useCase->execute(42, $dto, $this->mockUserPermission(edit: true));
        $this->assertSame('novoe-imya', (string) $result->slug);
    }

    #[Test]
    public function does_not_update_optional_fields_when_null(): void
    {
        $existing = $this->createExistingGallery();

        $this->galleryRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($existing);

        $this->galleryRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (GalleryEntity $gallery) {
                // description и isActive должны остаться прежними
                return $gallery->description === 'Старое описание'
                    && $gallery->isActive === true;
            }))
            ->andReturn($existing);

        $dto = new GalleryData(
            name: 'Новое название',
            slug: 'new-slug',
            description: null,
            isActive: null
        );

        $result = $this->useCase->execute(42, $dto, $this->mockUserPermission(edit: true));
        $this->assertSame('Старое описание', $result->description);
        $this->assertTrue($result->isActive);
    }

    #[Test]
    public function throws_access_denied_when_edit_permission_missing(): void
    {
        $dto = new GalleryData(name: 'Test', slug: 'test');

        $this->galleryRepo->shouldNotReceive('findById');
        $this->galleryRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_gallery_not_found(): void
    {
        $this->galleryRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new GalleryData(name: 'Test', slug: 'test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Галерея не найдена');
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
