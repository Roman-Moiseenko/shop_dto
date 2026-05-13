<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\DeleteContactUseCase;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use InvalidArgumentException;
use Tests\Trait\MockPermission;
class DeleteContactUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private DeleteContactUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new DeleteContactUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function deletes_contact_successfully(): void
    {
        $contactId = 42;
        $contact = new ContactEntity(new ContactType('phone'), '+123456789');
        $contact->id = $contactId;

        $this->contactRepo->shouldReceive('findById')
            ->with($contactId)
            ->once()
            ->andReturn($contact);

        $this->contactRepo->shouldReceive('delete')
            ->once()
            ->with($contactId);

        $this->useCase->execute($contactId, $this->mockUserPermission(delete: true));
        // Если исключений нет — успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $this->contactRepo->shouldNotReceive('findById');
        $this->contactRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission()); // delete: false
    }

    #[Test]
    public function throws_exception_when_contact_not_found(): void
    {
        $this->contactRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Контакт не найден');
        $this->useCase->execute(999, $this->mockUserPermission(delete: true));
    }
}
