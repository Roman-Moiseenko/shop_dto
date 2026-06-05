<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Client;
use App\Modules\Auth\Application\Actions\Client\CreateClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientCreateData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientAlreadyExistsException;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateClientUseCaseTest extends TestCase
{
    use MockPermission;
    private ClientRepositoryInterface $clientRepo;
    private CreateClientUseCase $useCase;
    function getModuleName(): string
    {
        return  'auth';
    }

    function getEntityName(): string
    {
        return 'buyer';
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepo = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new CreateClientUseCase($this->clientRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_client_without_consent(): void
    {
        $dto = new ClientCreateData(
            lastName: 'Иванов',
            firstName: 'Иван',
            email: 'ivan@example.com'
        );

        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->clientRepo->shouldReceive('save')->once()->andReturnUsing(function (ClientEntity $c) {
            $c->id = 5;
            return $c;
        });

        $permission = $this->mockUserPermission(create: true);
        $client = $this->useCase->execute($dto, $permission);

        $this->assertEquals(5, $client->id);
        $this->assertNull($client->dataConsent);
        $this->assertEquals('Иванов Иван', (string)$client->fullName);
    }

    public function test_throws_if_email_exists(): void
    {
        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(ClientAlreadyExistsException::class);
        $permission = $this->mockUserPermission(create: true);
        $this->useCase->execute(new ClientCreateData(lastName: 'Иван', firstName: 'Иван', email: 'used@example.com'), $permission);
    }
    public function test_throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission();
        $dto = new ClientCreateData(lastName: 'Иванов', firstName: 'Иван', email: 'client@test.ru');

        $this->clientRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }
}
