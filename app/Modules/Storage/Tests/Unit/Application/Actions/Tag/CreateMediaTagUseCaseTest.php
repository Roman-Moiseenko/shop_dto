<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Tag;

use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Tag\CreateMediaTagUseCase;
use App\Modules\Storage\Application\DTOs\Tag\MediaTagData;
use App\Modules\Storage\Application\Interfaces\MediaTagRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class CreateMediaTagUseCaseTest extends TestCase
{
    use MockPermission;

    private MediaTagRepositoryInterface $tagRepo;
    private CreateMediaTagUseCase $useCase;

    public function getModuleName(): string { return 'storage'; }
    public function getEntityName(): string { return 'media'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepo = Mockery::mock(MediaTagRepositoryInterface::class);
        $this->useCase = new CreateMediaTagUseCase($this->tagRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_tag_successfully(): void
    {
        $dto = new MediaTagData(name: 'Summer Sale', slug: 'summer-sale');

        $this->tagRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaTagEntity::class))
            ->andReturnUsing(function (MediaTagEntity $tag) {
                $tag->id = 42;
                return $tag;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));
        $this->assertEquals(42, $result->id);
        $this->assertSame('Summer Sale', $result->name->getValue());
        $this->assertSame('summer-sale', (string) $result->slug);
    }

    #[Test]
    public function throws_access_denied_when_create_permission_absent(): void
    {
        $dto = new MediaTagData(name: 'Test', slug: 'test');

        $this->tagRepo->shouldNotReceive('save');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // create: false
    }
}
