<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Media\UpdateMediaUseCase;
use App\Modules\Storage\Application\DTOs\Media\UpdateMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $repo;
    private UpdateMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new UpdateMediaUseCase($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createExistingMedia(): MediaEntity
    {
        $media = new MediaEntity(
            uuid: 'test-uuid',
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'test.jpg',
            disk: 'public',
            size: 100,
            title: 'Title',
            description: 'Description',
            sort: 0,
            mimeType: 'image/jpeg'
        );
        $media->id = 1;
        return $media;
    }

    public function test_updates_media_title_and_description(): void
    {
        $existing = $this->createExistingMedia();

        $this->repo->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($existing);

        $this->repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                return $media;
            });

        $permission = $this->mockUserPermission(edit: true);
        $dto = new UpdateMediaData(
            title: 'New Title',
            description: 'New Description'
        );

        $updated = $this->useCase->execute(1, $dto, $permission);

        $this->assertSame('New Title', $updated->title);
        $this->assertSame('New Description', $updated->description);
        $this->assertSame(0, $updated->sort); // не изменился
    }

    public function test_updates_sort_only(): void
    {
        $existing = $this->createExistingMedia();

        $this->repo->shouldReceive('findById')->once()->andReturn($existing);
        $this->repo->shouldReceive('save')->once()->andReturn($existing);

        $permission = $this->mockUserPermission(edit: true);
        $dto = new UpdateMediaData(sort: 5);

        $updated = $this->useCase->execute(1, $dto, $permission);
        $this->assertSame(5, $updated->sort);
        $this->assertSame('Title', $updated->title); // не изменился
    }

    public function test_throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission(edit: false);
        $dto = new UpdateMediaData(title: 'New Title');

        $this->repo->shouldNotReceive('findById');
        $this->repo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $dto, $permission);
    }

    public function test_throws_exception_when_media_not_found(): void
    {
        $this->repo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $permission = $this->mockUserPermission(edit: true);
        $dto = new UpdateMediaData(title: 'Title');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Медиа не найдено');
        $this->useCase->execute(999, $dto, $permission);
    }

}
