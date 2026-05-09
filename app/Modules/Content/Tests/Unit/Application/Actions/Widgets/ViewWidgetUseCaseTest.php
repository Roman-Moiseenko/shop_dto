<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;
use App\Modules\Content\Application\Actions\Widgets\ViewWidgetUseCase;
use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use App\Modules\Content\Infrastructure\Exceptions\WidgetNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ViewWidgetUseCaseTest extends TestCase
{
    use MockPermission;
    public function getModuleName(): string {return 'content';}
    public function getEntityName(): string {return 'data';}
    private WidgetRepositoryInterface $widgetRepo;
    private ViewWidgetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetRepo = Mockery::mock(WidgetRepositoryInterface::class);
        $this->useCase = new ViewWidgetUseCase($this->widgetRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    /** Создаёт реальный WidgetEntity для тестов */
    private function createWidgetEntity(int $id = 1): WidgetEntity
    {
        $widget = new WidgetEntity(
            'Text Block',
            'text-block',
            WidgetCategory::content(),
            new WidgetSchema(['type' => 'object', 'properties' => []])
        );
        $widget->id = $id;
        return $widget;
    }

    #[Test]
    public function returns_widget_when_permission_granted(): void
    {
        $widget = $this->createWidgetEntity(1);
        $this->widgetRepo->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($widget);

        $result = $this->useCase->execute(1, $this->mockUserPermission(view: true));
        $this->assertSame($widget, $result);
    }

    #[Test]
    public function throws_access_denied_when_permission_absent(): void
    {
        $this->widgetRepo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $this->mockUserPermission());
    }

    #[Test]
    public function throws_exception_when_widget_not_found(): void
    {
        $this->widgetRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(WidgetNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(view:true));
    }
}
