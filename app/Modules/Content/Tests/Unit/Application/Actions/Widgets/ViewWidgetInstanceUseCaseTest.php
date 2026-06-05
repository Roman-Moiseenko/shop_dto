<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;

use App\Modules\Content\Application\Actions\Widgets\ViewWidgetInstanceUseCase;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Domain\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ViewWidgetInstanceUseCaseTest extends TestCase
{
    use MockPermission;

    private WidgetInstanceRepositoryInterface $instanceRepo;
    private ViewWidgetInstanceUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->instanceRepo = Mockery::mock(WidgetInstanceRepositoryInterface::class);
        $this->useCase = new ViewWidgetInstanceUseCase($this->instanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_instance_when_found_and_view_permission_granted(): void
    {
        $instance = new WidgetInstanceEntity(1, []);
        $this->instanceRepo->shouldReceive('findById')
            ->with(10)
            ->once()
            ->andReturn($instance);

        $result = $this->useCase->execute(10, $this->mockUserPermission(view: true));
        $this->assertSame($instance, $result);
    }

    #[Test]
    public function throws_access_denied_without_view_permission(): void
    {
        $this->instanceRepo->shouldNotReceive('findById');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $this->mockUserPermission());
    }

    #[Test]
    public function throws_exception_when_instance_not_found(): void
    {
        $this->instanceRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(WidgetInstanceNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(view: true));
    }
}
