<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Gallery;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Gallery\ListGalleryTagsUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ListGalleryTagsUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaRepositoryInterface $mediaRepo;
    private ListGalleryTagsUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new ListGalleryTagsUseCase($this->mediaRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_distinct_tags_when_view_permission_granted(): void
    {
        $galleryId = 10;
        $tag1 = new MediaTagEntity(new TagName('Лето'), new Slug('summer'));
        $tag1->id = 1;
        $tag2 = new MediaTagEntity(new TagName('Скидки'), new Slug('discount'));
        $tag2->id = 2;
        $expectedTags = [$tag1, $tag2];

        $this->mediaRepo->shouldReceive('getDistinctTagsByEntity')
            ->with('storage.gallery', $galleryId)
            ->once()
            ->andReturn($expectedTags);

        $result = $this->useCase->execute($galleryId, $this->mockUserPermission(view: true));

        $this->assertSame($expectedTags, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->mediaRepo->shouldNotReceive('getDistinctTagsByEntity');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // view: false
    }
}
