<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Tag;

use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Actions\Tag\IndexMediaTagsUseCase;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexMediaTagsUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaTagRepositoryInterface $tagRepo;
    private IndexMediaTagsUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepo = Mockery::mock(MediaTagRepositoryInterface::class);
        $this->useCase = new IndexMediaTagsUseCase($this->tagRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_all_tags_when_view_permission_granted(): void
    {
        $tag = new MediaTagEntity(new TagName('Summer Sale'), new Slug('summer-sale'));
        $this->tagRepo->shouldReceive('all')->once()->andReturn([$tag]);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));
        $this->assertSame([$tag], $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->tagRepo->shouldNotReceive('all');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
