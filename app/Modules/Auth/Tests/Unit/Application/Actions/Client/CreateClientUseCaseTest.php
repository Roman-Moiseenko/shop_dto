<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Client;
use App\Modules\Auth\Application\Actions\Client\CreateClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientCreateData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Infrastructure\Exceptions\ClientAlreadyExistsException;
use PHPUnit\Framework\TestCase;
use Mockery;
class CreateClientUseCaseTest extends TestCase
{
    private ClientRepositoryInterface $clientRepo;
    private CreateClientUseCase $useCase;

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

        $client = $this->useCase->execute($dto);

        $this->assertEquals(5, $client->id);
        $this->assertNull($client->dataConsent);
        $this->assertEquals('Иванов Иван', (string)$client->fullName);
    }

    public function test_throws_if_email_exists(): void
    {
        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(ClientAlreadyExistsException::class);
        $this->useCase->execute(new ClientCreateData(lastName: 'Иван', firstName: 'Иван', email: 'used@example.com'));
    }
}
