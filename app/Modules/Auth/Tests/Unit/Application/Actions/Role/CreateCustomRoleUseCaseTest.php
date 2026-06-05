<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Role;
use App\Modules\Auth\Application\Actions\Role\CreateCustomRoleUseCase;
use App\Modules\Auth\Application\DTOs\Role\RoleCreateData;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Shared\Application\Interfaces\TransactionManagerInterface;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Spatie\Permission\Models\Role;
use Tests\Trait\MockPermission;

class CreateCustomRoleUseCaseTest extends TestCase
{
    use MockPermission;
    private RoleRepositoryInterface $repo;
    private TransactionManagerInterface $transaction;
    private CreateCustomRoleUseCase $useCase;
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
        $this->transaction = Mockery::mock(TransactionManagerInterface::class);
        // Мок транзакции просто выполняет колбэк
        $this->transaction->shouldReceive('execute')
            ->andReturnUsing(fn($callback) => $callback());

        $this->useCase = new CreateCustomRoleUseCase($this->repo, $this->transaction);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_custom_role(): void
    {
        $dto = new RoleCreateData(name: 'Test', permissions: [], description: 'desc');
        $mockRole = Mockery::mock(Role::class);
        // permissions пуст, syncPermissions не должен вызываться
        $mockRole->shouldReceive('syncPermissions')->never();

        $this->repo->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Test',
                'guard_name' => 'api',
                'is_system' => false,
                'description' => 'desc',
            ])
            ->andReturn($mockRole);
        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);
        $this->assertSame($mockRole, $result);
    }

    public function test_creates_custom_role_with_permissions(): void
    {
        $dto = new RoleCreateData(name: 'Manager', permissions: ['view-orders'], description: '');
        $mockRole = Mockery::mock(Role::class);
        $mockRole->shouldReceive('syncPermissions')
            ->once()
            ->with(['view-orders'])
            ->andReturnSelf();

        $this->repo->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Manager',
                'guard_name' => 'api',
                'is_system' => false,
                'description' => '',
            ])
            ->andReturn($mockRole);
        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);
        $this->assertSame($mockRole, $result);
    }
    public function test_throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission(create: false);
        $dto = new RoleCreateData(name: 'Test', permissions: [], description: '');

        $this->repo->shouldNotReceive('create');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }
}
