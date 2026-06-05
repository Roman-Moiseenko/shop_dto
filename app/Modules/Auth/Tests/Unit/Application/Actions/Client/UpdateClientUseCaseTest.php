<?php

namespace App\Modules\Auth\Tests\Unit\Application\Actions\Client;
use App\Modules\Auth\Application\Actions\Client\UpdateClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientUpdateData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use App\Modules\Auth\Domain\Exceptions\ClientAlreadyExistsException;
use App\Modules\Auth\Domain\ValueObjects\Email;
use App\Modules\Auth\Domain\ValueObjects\FullName;
use App\Modules\Auth\Domain\ValueObjects\PhoneNumber;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateClientUseCaseTest extends TestCase
{
    use MockPermission;
    private ClientRepositoryInterface $clientRepo;
    private UserRepositoryInterface $userRepo;
    private UpdateClientUseCase $useCase;
    private ClientEntity $client;
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
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new UpdateClientUseCase($this->clientRepo, $this->userRepo);

        $this->client = new ClientEntity(
            new FullName('Иванов Иван Иванович'),
            new Email('ivan@example.com')
        );
        $this->client->id = 10;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    private function createExistingClient(): ClientEntity
    {
        $client = new ClientEntity(
            fullName: new FullName('Иванов Иван Иванович'),
            email: new Email('client@example.com'),
            phone: new PhoneNumber('+79001234567')
        );
        $client->id = 10;
        // Опционально можно установить адрес, дату рождения и т.д., но для теста на отказ в доступе не обязательно
        return $client;
    }
    public function test_updates_all_fields(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(10)->once()->andReturn($this->client);
        $this->clientRepo->shouldReceive('emailExists')->with(Mockery::any(), 10)->once()->andReturn(false);
        $this->clientRepo->shouldReceive('phoneExists')->with(Mockery::any(), 10)->once()->andReturn(false);
        $this->userRepo->shouldReceive('emailExists')->once()->andReturn(false);
        $this->clientRepo->shouldReceive('save')->once()->andReturn($this->client);

        $dto = new ClientUpdateData(
            lastName: 'Петров',
            firstName: 'Пётр',
            middleName: 'Петрович',
            phone: '+79001112233',
            email: 'petrov@example.com',
            birthDate: '1990-01-01',
            gender: 'male',
            country: 'Россия',
            region: 'Московская обл.',
            city: 'Москва',
            street: 'Тверская',
            postalCode: '125009'
        );
        $permission = $this->mockUserPermission(edit: true);
        $updated = $this->useCase->execute(10, $dto, $permission);

        $this->assertEquals('Петров Пётр Петрович', (string)$updated->fullName);
        $this->assertEquals('petrov@example.com', (string)$updated->email);
        $this->assertEquals('+79001112233', (string)$updated->phone);
        $this->assertNotNull($updated->address);
        $this->assertEquals('Москва', $updated->address->city);
    }

    public function test_throws_if_email_duplicate(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(10)->once()->andReturn($this->client);
        $this->clientRepo->shouldReceive('emailExists')->once()->andReturn(true);
        $this->expectException(ClientAlreadyExistsException::class);
        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute(10, new ClientUpdateData(lastName: 'Иванов', firstName: 'Иван', email: 'used@example.com'), $permission);
    }

    public function test_throws_if_phone_duplicate(): void
    {
        $this->clientRepo->shouldReceive('findById')->with(10)->once()->andReturn($this->client);
        $this->clientRepo->shouldReceive('phoneExists')->once()->andReturn(true);
        $this->expectException(ClientAlreadyExistsException::class);
        $permission = $this->mockUserPermission(edit: true);
        $this->useCase->execute(10, new ClientUpdateData(lastName: 'Иванов', firstName: 'Иван', phone: '+79998887766'), $permission);
    }

    public function test_throws_access_denied_when_missing_permission(): void
    {
        $client = $this->createExistingClient();
        $this->clientRepo->shouldReceive('findById')->with(10)->andReturn($client);
        $this->clientRepo->shouldNotReceive('save');

        $permission = $this->mockUserPermission(edit: false);
        $dto = new ClientUpdateData(lastName: 'Иванов', firstName: 'Иван');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $dto, $permission);
    }
}
