<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Role;
use App\Modules\Auth\Application\Actions\Role\DeleteCustomRoleUseCase;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\TestCase;
use Mockery;

class DeleteCustomRoleUseCaseTest extends TestCase
{
    private RoleRepositoryInterface $repo;
    private DeleteCustomRoleUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(RoleRepositoryInterface::class);
        $this->useCase = new DeleteCustomRoleUseCase($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_deletes_custom_role(): void
    {
        $roleId = 2;
        $existingRole = Mockery::mock(Role::class);
        $existingRole->shouldReceive('getAttribute')
            ->with('is_system')
            ->andReturn(false);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn($existingRole);

        $this->repo->shouldReceive('delete')
            ->once()
            ->with($roleId)
            ->andReturn(true);

        $this->useCase->execute($roleId);
        // Если исключения нет – тест пройден
        $this->assertTrue(true);
    }

    #[Test]
    public function it_throws_exception_when_deleting_system_role(): void
    {
        $roleId = 1;
        $existingRole = Mockery::mock(Role::class);
        $existingRole->shouldReceive('getAttribute')
            ->with('is_system')
            ->andReturn(true);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn($existingRole);

        $this->repo->shouldNotReceive('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя удалить системную роль');

        $this->useCase->execute($roleId);
    }

    #[Test]
    public function it_throws_exception_if_role_not_found(): void
    {
        $roleId = 999;
        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn(null);

        $this->repo->shouldNotReceive('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Роль не найдена');

        $this->useCase->execute($roleId);
    }
}
