<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Role;
use App\Modules\Auth\Application\Actions\Role\UpdateCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleUpdateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\Permission\Models\Role;
use Tests\Trait\MockPermission;

class UpdateCustomRoleUseCaseTest extends TestCase
{
    use MockPermission;
    private RoleRepositoryInterface $repo;
    private UpdateCustomRoleUseCase $useCase;
    function getModuleName(): string
    {
        return  'auth';
    }
    function getEntityName(): string
    {
        return 'settings';
    }
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
        $dto = new RoleUpdateData(
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
        $permission = $this->mockUserPermission(edit: true);
        $result = $this->useCase->execute($roleId, $dto, $permission);
        $this->assertSame($updatedRoleMock, $result);
    }

    #[Test]
    public function it_throws_exception_when_updating_system_role(): void
    {
        $roleId = 2;
        $dto = new RoleUpdateData(name: 'Admin', permissions: [], description: '');

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
        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute($roleId, $dto, $permission);
    }

    #[Test]
    public function it_throws_exception_if_role_not_found(): void
    {
        $roleId = 999;
        $dto = new RoleUpdateData(name: 'Nonexistent', permissions: [], description: '');

        $this->repo->shouldReceive('findById')
            ->once()
            ->with($roleId)
            ->andReturn(null);

        $this->repo->shouldNotReceive('update');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Роль не найдена');
        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute($roleId, $dto, $permission);
    }
    public function test_throws_access_denied_when_missing_permission(): void
    {
        $roleId = 1;
        $permission = $this->mockUserPermission(edit: false);
        $dto = new RoleUpdateData(name: 'Role', permissions: [], description: '');

        $this->repo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($roleId, $dto, $permission);
    }
}
