<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;
use App\Modules\Content\Application\Actions\Widgets\IndexWidgetInstanceUseCase;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexWidgetInstanceUseCaseTest extends TestCase
{
    use MockPermission;

    private WidgetInstanceRepositoryInterface $instanceRepo;
    private IndexWidgetInstanceUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->instanceRepo = Mockery::mock(WidgetInstanceRepositoryInterface::class);
        $this->useCase = new IndexWidgetInstanceUseCase($this->instanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_instances_when_view_permission_granted(): void
    {
        $instances = [new WidgetInstanceEntity(1, [])];
        $this->instanceRepo->shouldReceive('all')
            ->with(null)
            ->once()
            ->andReturn($instances);

        $result = $this->useCase->execute(null, $this->mockUserPermission(view: true));
        $this->assertSame($instances, $result);
    }

    #[Test]
    public function filters_by_widget_id_when_provided(): void
    {
        $this->instanceRepo->shouldReceive('all')
            ->with(5)
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute(5, $this->mockUserPermission(view: true));
        $this->assertSame([], $result);
    }

    #[Test]
    public function throws_access_denied_without_view_permission(): void
    {
        $this->instanceRepo->shouldNotReceive('all');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(null, $this->mockUserPermission());
    }
}
