<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Tag;

use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Tag\ViewMediaTagUseCase;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;
use Tests\Trait\MockPermission;

class ViewMediaTagUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaTagRepositoryInterface $tagRepo;
    private ViewMediaTagUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepo = Mockery::mock(MediaTagRepositoryInterface::class);
        $this->useCase = new ViewMediaTagUseCase($this->tagRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createTag(): MediaTagEntity
    {
        $tag = new MediaTagEntity(new TagName('Summer Sale'), new Slug('summer-sale'));
        $tag->id = 10;
        return $tag;
    }

    #[Test]
    public function returns_tag_when_found_and_view_permission_granted(): void
    {
        $tag = $this->createTag();
        $this->tagRepo->shouldReceive('findById')->with(10)->once()->andReturn($tag);

        $result = $this->useCase->execute(10, $this->mockUserPermission(view: true));
        $this->assertSame($tag, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->tagRepo->shouldNotReceive('findById');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission()); // view: false
    }

    #[Test]
    public function throws_exception_when_tag_not_found(): void
    {
        $this->tagRepo->shouldReceive('findById')->with(999)->once()->andReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->useCase->execute(999, $this->mockUserPermission(view: true));
    }
}
