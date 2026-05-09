<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;
use App\Modules\Content\Application\Actions\Widgets\IndexWidgetUseCase;
use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexWidgetUseCaseTest extends TestCase
{
    use MockPermission;
    public function getModuleName(): string {return 'content';}
    public function getEntityName(): string {return 'data';}
    private WidgetRepositoryInterface $widgetRepo;
    private IndexWidgetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetRepo = Mockery::mock(WidgetRepositoryInterface::class);
        $this->useCase = new IndexWidgetUseCase($this->widgetRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createWidgetEntity(string $name = 'Widget'): WidgetEntity
    {
        return new WidgetEntity(
            $name,
            'test-widget',
            WidgetCategory::content(),
            new WidgetSchema(['type' => 'object', 'properties' => []])
        );
    }
    #[Test]
    public function returns_widget_list_when_permission_granted(): void
    {
        $widgets = [
            $this->createWidgetEntity('Widget A'),
            $this->createWidgetEntity('Widget B'),
        ];
        $this->widgetRepo->shouldReceive('all')
            ->once()
            ->andReturn($widgets);

        $result = $this->useCase->execute($this->mockUserPermission(true));
        $this->assertSame($widgets, $result);
    }


    #[Test]
    public function throws_access_denied_when_permission_absent(): void
    {
        $this->widgetRepo->shouldNotReceive('all');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission());
    }
}
