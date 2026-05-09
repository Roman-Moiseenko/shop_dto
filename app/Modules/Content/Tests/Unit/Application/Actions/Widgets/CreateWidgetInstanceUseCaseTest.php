<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;

use App\Modules\Content\Application\Actions\Widgets\CreateWidgetInstanceUseCase;
use App\Modules\Content\Application\DTOs\WidgetInstanceData;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateWidgetInstanceUseCaseTest extends TestCase
{
    use MockPermission;

    private WidgetInstanceRepositoryInterface $instanceRepo;
    private CreateWidgetInstanceUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->instanceRepo = Mockery::mock(WidgetInstanceRepositoryInterface::class);
        $this->useCase = new CreateWidgetInstanceUseCase($this->instanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_instance_with_valid_data_and_create_permission(): void
    {
        $dto = new WidgetInstanceData(
            widget_id: 7,
            params: ['title' => 'Hero Banner'],
            title: 'My Instance'
        );

        $this->instanceRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(WidgetInstanceEntity::class))
            ->andReturnUsing(function (WidgetInstanceEntity $instance) {
                $instance->id = 42;
                return $instance;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));
        $this->assertSame(42, $result->id);
        $this->assertSame('My Instance', $result->title);
        $this->assertSame(['title' => 'Hero Banner'], $result->params);
    }

    #[Test]
    public function throws_access_denied_without_create_permission(): void
    {
        $dto = new WidgetInstanceData(widget_id: 1, params: []);
        $this->instanceRepo->shouldNotReceive('save');
        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission());
    }
}
