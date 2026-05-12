<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Tag;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Tag\UpdateMediaTagUseCase;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagData;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;
use Tests\Trait\MockPermission;

class UpdateMediaTagUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaTagRepositoryInterface $tagRepo;
    private UpdateMediaTagUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepo = Mockery::mock(MediaTagRepositoryInterface::class);
        $this->useCase = new UpdateMediaTagUseCase($this->tagRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTag(): MediaTagEntity
    {
        $tag = new MediaTagEntity(new TagName('Old'), new Slug('old'));
        $tag->id = 5;
        return $tag;
    }

    #[Test]
    public function updates_tag_successfully(): void
    {
        $existing = $this->createTag();
        $this->tagRepo->shouldReceive('findById')->with(5)->once()->andReturn($existing);
        $this->tagRepo->shouldReceive('save')->once()->with(Mockery::type(MediaTagEntity::class))->andReturn($existing);

        $dto = new MediaTagData(name: 'New Name', slug: 'new-slug');
        $result = $this->useCase->execute(5, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New Name', $result->name->getValue());
        $this->assertSame('new-slug', (string) $result->slug);
    }

    #[Test]
    public function throws_access_denied_when_edit_permission_absent(): void
    {
        $dto = new MediaTagData(name: 'Test', slug: 'test');
        $this->tagRepo->shouldNotReceive('findById');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(5, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_tag_not_found(): void
    {
        $this->tagRepo->shouldReceive('findById')->with(999)->once()->andReturn(null);
        $dto = new MediaTagData(name: 'Test', slug: 'test');
        $this->expectException(InvalidArgumentException::class);
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
