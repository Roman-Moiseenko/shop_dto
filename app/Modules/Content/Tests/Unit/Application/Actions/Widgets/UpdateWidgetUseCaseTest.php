<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;
use App\Modules\Content\Application\Actions\Widgets\UpdateWidgetUseCase;
use App\Modules\Content\Application\DTOs\WidgetUpdateData;
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

class UpdateWidgetUseCaseTest extends TestCase
{
    use MockPermission;
    public function getModuleName(): string {return 'content';}
    public function getEntityName(): string {return 'settings';}
    private WidgetRepositoryInterface $widgetRepo;
    private UpdateWidgetUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->widgetRepo = Mockery::mock(WidgetRepositoryInterface::class);
        $this->useCase = new UpdateWidgetUseCase($this->widgetRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    private function createWidgetEntity(): WidgetEntity
    {
        $widget = new WidgetEntity(
            'Old Name',
            'old-slug',
            WidgetCategory::content(),
            new WidgetSchema(['type' => 'object', 'properties' => []])
        );
        $widget->id = 1;
        return $widget;
    }

    #[Test]
    public function updates_widget_successfully(): void
    {
        $existing = $this->createWidgetEntity();
        $this->widgetRepo->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($existing);

        $this->widgetRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(WidgetEntity::class))
            ->andReturn($existing);

        $dto = new WidgetUpdateData(
            name: 'New Name',
            slug: 'new-slug',
            category: 'media',
            schema: ['type' => 'object', 'properties' => ['text' => ['type' => 'string']]],
            description: 'Updated description'
        );

        $result = $this->useCase->execute(1, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame('New Name', $result->name);
        $this->assertSame('new-slug', $result->slug);
        $this->assertSame('media', $result->category->getValue());
        $this->assertSame('Updated description', $result->description);
    }

    #[Test]
    public function throws_access_denied_when_permission_absent(): void
    {
        $dto = new WidgetUpdateData(
            name: 'New Name',
            slug: 'new-slug',
            category: 'content',
            schema: ['type' => 'object', 'properties' => []],
        );

        $this->widgetRepo->shouldNotReceive('findById');
        $this->widgetRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $dto, $this->mockUserPermission());
    }

    #[Test]
    public function throws_exception_when_widget_not_found(): void
    {
        $this->widgetRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new WidgetUpdateData(
            name: 'New Name',
            slug: 'new-slug',
            category: 'content',
            schema: ['type' => 'object', 'properties' => []],
        );

        $this->expectException(WidgetNotFoundException::class);
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
