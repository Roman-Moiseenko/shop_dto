<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\ViewContactUseCase;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ViewContactUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private ViewContactUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new ViewContactUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_contact_when_found_and_view_permission_granted(): void
    {
        $contactId = 42;
        $contact = new ContactEntity(new ContactType('phone'), '+79991234567');
        $contact->id = $contactId;

        $this->contactRepo->shouldReceive('findById')
            ->with($contactId)
            ->once()
            ->andReturn($contact);

        $result = $this->useCase->execute($contactId, $this->mockUserPermission(view: true));
        $this->assertSame($contact, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->contactRepo->shouldNotReceive('findById');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(42, $this->mockUserPermission()); // view: false
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
        $this->useCase->execute(999, $this->mockUserPermission(view: true));
    }
}
