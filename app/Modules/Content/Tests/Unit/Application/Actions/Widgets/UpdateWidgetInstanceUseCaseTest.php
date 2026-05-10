<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;

use App\Modules\Content\Application\Actions\Widgets\UpdateWidgetInstanceUseCase;
use App\Modules\Content\Application\DTOs\Widget\WidgetInstanceData;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Infrastructure\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateWidgetInstanceUseCaseTest extends TestCase
{
    use MockPermission;

    private WidgetInstanceRepositoryInterface $instanceRepo;
    private UpdateWidgetInstanceUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->instanceRepo = Mockery::mock(WidgetInstanceRepositoryInterface::class);
        $this->useCase = new UpdateWidgetInstanceUseCase($this->instanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function updates_instance_with_edit_permission(): void
    {
        $existing = new WidgetInstanceEntity(1, ['old' => true]);
        $existing->id = 5;

        $this->instanceRepo->shouldReceive('findById')
            ->with(5)
            ->once()
            ->andReturn($existing);

        $this->instanceRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(WidgetInstanceEntity::class))
            ->andReturn($existing);

        $dto = new WidgetInstanceData(
            widget_id: 1,
            params: ['new' => true],
            title: 'Updated'
        );

        $result = $this->useCase->execute(5, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame(['new' => true], $result->params);
        $this->assertSame('Updated', $result->title);
    }

    #[Test]
    public function throws_access_denied_without_edit_permission(): void
    {
        $dto = new WidgetInstanceData(widget_id: 1, params: []);
        $this->instanceRepo->shouldNotReceive('findById');
        $this->instanceRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(5, $dto, $this->mockUserPermission());
    }

    #[Test]
    public function throws_exception_when_instance_not_found(): void
    {
        $this->instanceRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new WidgetInstanceData(widget_id: 1, params: []);

        $this->expectException(WidgetInstanceNotFoundException::class);
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
