<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Role;
use App\Modules\Auth\Application\Actions\Role\DeleteCustomRoleUseCase;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Spatie\Permission\Models\Role;
use Tests\Trait\MockPermission;

class DeleteCustomRoleUseCaseTest extends TestCase
{
    use MockPermission;
    private RoleRepositoryInterface $repo;
    private DeleteCustomRoleUseCase $useCase;
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
        $permission = $this->mockUserPermission(delete: true);
        $this->useCase->execute($roleId, $permission);
        // Если исключения нет – тест пройден
        $this->assertTrue(true);
    }
    #[Test]
    public function it_throws_access_denied_when_missing_permission(): void
    {
        $roleId = 2;
        $permission = $this->mockUserPermission(delete: false); // или просто mockUserPermission() без аргументов, если по умолчанию false

        // Репозиторий не должен вызывать ни findById, ни delete
        $this->repo->shouldNotReceive('findById');
        $this->repo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($roleId, $permission);
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
        $permission = $this->mockUserPermission(delete: true);
        $this->useCase->execute($roleId, $permission);
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
        $permission = $this->mockUserPermission(delete: true);
        $this->useCase->execute($roleId, $permission);
    }
    public function test_throws_access_denied_when_missing_permission(): void
    {
        $roleId = 2;
        $permission = $this->mockUserPermission();

        $this->repo->shouldNotReceive('findById');
        $this->repo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($roleId, $permission);
    }
}
