<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;

use App\Modules\Content\Application\Actions\Widgets\DeleteWidgetUseCase;
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

class DeleteWidgetUseCaseTest extends TestCase
{
    use MockPermission;
    public function getModuleName(): string {return 'content';}
    public function getEntityName(): string {return 'settings';}

    private WidgetRepositoryInterface $widgetRepo;
    private DeleteWidgetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetRepo = Mockery::mock(WidgetRepositoryInterface::class);
        $this->useCase = new DeleteWidgetUseCase($this->widgetRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createWidgetEntity(): WidgetEntity
    {
        $widget = new WidgetEntity(
            'To Delete',
            'to-delete',
            WidgetCategory::content(),
            new WidgetSchema(['type' => 'object', 'properties' => []])
        );
        $widget->id = 1;
        return $widget;
    }

    #[Test]
    public function deletes_widget_successfully(): void
    {
        $existing = $this->createWidgetEntity();
        $this->widgetRepo->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($existing);

        $this->widgetRepo->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturn(true);

        $this->useCase->execute(1, $this->mockUserPermission(delete: true));
        // Если исключения не было, тест пройден
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_absent(): void
    {
        $this->widgetRepo->shouldNotReceive('findById');
        $this->widgetRepo->shouldNotReceive('delete');

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

        $this->widgetRepo->shouldNotReceive('delete');

        $this->expectException(WidgetNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }

}
