<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Tag;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Tag\DeleteMediaTagUseCase;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DeleteMediaTagUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaTagRepositoryInterface $tagRepo;
    private DeleteMediaTagUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepo = Mockery::mock(MediaTagRepositoryInterface::class);
        $this->useCase = new DeleteMediaTagUseCase($this->tagRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTag(): MediaTagEntity
    {
        $tag = new MediaTagEntity(new TagName('To Delete'), new Slug('to-delete'));
        $tag->id = 3;
        return $tag;
    }

    #[Test]
    public function deletes_tag_successfully(): void
    {
        $existing = $this->createTag();
        $this->tagRepo->shouldReceive('findById')->with(3)->once()->andReturn($existing);
        $this->tagRepo->shouldReceive('delete')->with(3)->once();

        $this->useCase->execute(3, $this->mockUserPermission(delete: true));
        $this->assertTrue(true); // no exception thrown
    }

    #[Test]
    public function throws_access_denied_when_delete_permission_absent(): void
    {
        $this->tagRepo->shouldNotReceive('findById');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(3, $this->mockUserPermission()); // delete: false
    }

    #[Test]
    public function throws_exception_when_tag_not_found(): void
    {
        $this->tagRepo->shouldReceive('findById')->with(999)->once()->andReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }
}
