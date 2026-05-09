<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Widgets;

use App\Modules\Content\Application\Actions\Widgets\DeleteWidgetInstanceUseCase;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Infrastructure\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DeleteWidgetInstanceUseCaseTest extends TestCase
{
    use MockPermission;

    private WidgetInstanceRepositoryInterface $instanceRepo;
    private DeleteWidgetInstanceUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->instanceRepo = Mockery::mock(WidgetInstanceRepositoryInterface::class);
        $this->useCase = new DeleteWidgetInstanceUseCase($this->instanceRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function deletes_instance_with_delete_permission(): void
    {
        $existing = new WidgetInstanceEntity(1, []);
        $existing->id = 8;

        $this->instanceRepo->shouldReceive('findById')
            ->with(8)
            ->once()
            ->andReturn($existing);

        $this->instanceRepo->shouldReceive('delete')
            ->with(8)
            ->once()
            ->andReturn(true);

        $this->useCase->execute(8, $this->mockUserPermission(delete: true));
        $this->assertTrue(true); // не было исключений
    }

    #[Test]
    public function throws_access_denied_without_delete_permission(): void
    {
        $this->instanceRepo->shouldNotReceive('findById');
        $this->instanceRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(8, $this->mockUserPermission());
    }

    #[Test]
    public function throws_exception_when_instance_not_found(): void
    {
        $this->instanceRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(WidgetInstanceNotFoundException::class);
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }
}
