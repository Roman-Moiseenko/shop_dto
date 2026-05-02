<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Role;
use App\Modules\Auth\Application\Actions\Role\UpdateCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\TestCase;
use Mockery;

class UpdateCustomRoleUseCaseTest extends TestCase
{
    private RoleRepositoryInterface $repo;
    private UpdateCustomRoleUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(RoleRepositoryInterface::class);
        $this->useCase = new UpdateCustomRoleUseCase($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_updates_custom_role_with_permissions(): void
    {
        $roleId = 1;
        $dto = new RoleCreateData(
            name: 'Updated Role',
            permissions: ['view-orders', 'edit-orders'],
            description: 'Updated description'
        );

        // Роль из репозитория (кастомная)
        $existingRole = Mockery::mock(Role::class);
        $existingRole->shouldReceive('getAttribute')
            ->with('is_system')
            ->andReturn(false);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn($existingRole);

        // Репозиторий обновляет роль и возвращает новую (или ту же) модель
        $updatedRoleMock = Mockery::mock(Role::class);
        $this->repo->shouldReceive('update')
            ->once()
            ->with($roleId, [
                'name' => 'Updated Role',
                'description' => 'Updated description',
            ])
            ->andReturn($updatedRoleMock);

        // Ожидаем синхронизацию разрешений на обновлённой роли
        $updatedRoleMock->shouldReceive('syncPermissions')
            ->once()
            ->with(['view-orders', 'edit-orders'])
            ->andReturnSelf();

        $result = $this->useCase->execute($roleId, $dto);
        $this->assertSame($updatedRoleMock, $result);
    }

    #[Test]
    public function it_throws_exception_when_updating_system_role(): void
    {
        $roleId = 2;
        $dto = new RoleCreateData(name: 'Admin', permissions: [], description: '');

        $existingRole = Mockery::mock(Role::class);
        $existingRole->shouldReceive('getAttribute')
            ->with('is_system')
            ->andReturn(true);

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn($existingRole);

        $this->repo->shouldNotReceive('update');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Нельзя редактировать системную роль');

        $this->useCase->execute($roleId, $dto);
    }

    #[Test]
    public function it_throws_exception_if_role_not_found(): void
    {
        $roleId = 999;
        $dto = new RoleCreateData(name: 'Nonexistent', permissions: [], description: '');

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn(null);

        $this->repo->shouldNotReceive('update');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Роль не найдена');

        $this->useCase->execute($roleId, $dto);
    }
}
