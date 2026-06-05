<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\CreateContactUseCase;
use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class CreateContactUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private CreateContactUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new CreateContactUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function creates_contact_with_full_data_successfully(): void
    {
        $dto = new ContactData(
            type: 'phone',
            value: '+79991234567',
            link: 'tel:+79991234567',
            iconUuid: 'icon-uuid',
            caption: 'Звоните',
            analyticsField: 'main-phone',
        );

        $this->contactRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (ContactEntity $contact) {
                return $contact->type->getValue() === 'phone'
                    && $contact->value === '+79991234567'
                    && $contact->link === 'tel:+79991234567'
                    && $contact->iconUuid === 'icon-uuid'
                    && $contact->caption === 'Звоните'
                    && $contact->analyticsField === 'main-phone'
                    && $contact->isActive === false
                    && $contact->sort === 0;
            }))
            ->andReturnUsing(function (ContactEntity $contact) {
                $contact->id = 42;
                return $contact;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertSame(42, $result->id);
        $this->assertSame('phone', $result->type->getValue());
        $this->assertSame('+79991234567', $result->value);
        $this->assertFalse($result->isActive);
        $this->assertSame(0, $result->sort);
    }

    #[Test]
    public function creates_contact_with_minimal_data_and_defaults(): void
    {
        $dto = new ContactData(
            type: 'email',
            value: 'test@example.com',
        );

        $this->contactRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (ContactEntity $contact) {
                return $contact->type->getValue() === 'email'
                    && $contact->value === 'test@example.com'
                    && $contact->link === null
                    && $contact->iconUuid === null
                    && $contact->caption === null
                    && $contact->analyticsField === null
                    && $contact->isActive === false
                    && $contact->sort === 0;
            }))
            ->andReturnUsing(function (ContactEntity $contact) {
                $contact->id = 1;
                return $contact;
            });

        $result = $this->useCase->execute($dto, $this->mockUserPermission(create: true));

        $this->assertSame(1, $result->id);
        $this->assertSame('email', $result->type->getValue());
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new ContactData(type: 'phone', value: '123');

        $this->contactRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // create: false
    }
}
