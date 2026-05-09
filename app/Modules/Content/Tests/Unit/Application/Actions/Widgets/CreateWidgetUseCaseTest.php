<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;
use App\Modules\Content\Application\Actions\Widgets\CreateWidgetUseCase;
use App\Modules\Content\Application\DTOs\WidgetData;
use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateWidgetUseCaseTest extends TestCase
{
    use MockPermission;
    public function getModuleName(): string {return 'content';}
    public function getEntityName(): string {return 'settings';}
    private WidgetRepositoryInterface $widgetRepo;
    private CreateWidgetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetRepo = Mockery::mock(WidgetRepositoryInterface::class);
        $this->useCase = new CreateWidgetUseCase($this->widgetRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_widget_successfully(): void
    {
        $dto = new WidgetData(
            name: 'Text Block',
            slug: 'text-block',
            category: 'content',
            schema: ['type' => 'object', 'properties' => []],
            description: null
        );

        $this->widgetRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(WidgetEntity::class))
            ->andReturnUsing(function (WidgetEntity $widget) {
                $widget->id = 42;
                return $widget;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));
        $this->assertEquals(42, $result->id);
        $this->assertEquals('Text Block', $result->name);
    }

    #[Test]
    public function throws_access_denied_when_permission_absent(): void
    {
        $dto = new WidgetData(
            name: 'Test',
            slug: 'test',
            category: 'content',
            schema: ['type' => 'object', 'properties' => []],
        );

        $this->widgetRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission());
    }
}
