<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Client;
use App\Modules\Auth\Application\Actions\Client\CreateClientWithConsentUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientCreateWithConsentData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientAlreadyExistsException;
use Mockery;
use PHPUnit\Framework\TestCase;

class CreateClientWithConsentUseCaseTest extends TestCase
{
    private ClientRepositoryInterface $clientRepo;
    private CreateClientWithConsentUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientRepo = Mockery::mock(ClientRepositoryInterface::class);
        $this->useCase = new CreateClientWithConsentUseCase($this->clientRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_client_with_consent(): void
    {
        $dto = new ClientCreateWithConsentData(
            lastName: 'Иванов',
            firstName: 'Иван',
            email: 'ivan@example.com',
            policyVersion: 'v1',
            actionIdentifier: 'ip_127.0.0.1',
            phone: '+79001234567'
        );

        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->clientRepo->shouldReceive('phoneExists')->once()->andReturn(false);
        $this->clientRepo->shouldReceive('save')->once()->andReturnUsing(function (ClientEntity $c) {
            $c->id = 33;
            return $c;
        });

        $client = $this->useCase->execute($dto);

        $this->assertEquals(33, $client->id);
        $this->assertNotNull($client->dataConsent);
        $this->assertTrue($client->dataConsent->active);
        // Поскольку client->dataConsent создаётся как активный, проверяем true
        $this->assertTrue($client->dataConsent->active);
        $this->assertEquals('v1', $client->dataConsent->policyVersion);
    }

    public function test_throws_if_email_exists(): void
    {
        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(ClientAlreadyExistsException::class);
        $this->useCase->execute(new ClientCreateWithConsentData(
            lastName: 'Иван', firstName: 'Иван', email: 'used@example.com',
            policyVersion: 'v1', actionIdentifier: 'ip'
        ));
    }
}
