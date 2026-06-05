<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\UpdateContactUseCase;
use App\Modules\Content\Application\DTOs\Contact\ContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class UpdateContactUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private UpdateContactUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new UpdateContactUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function updates_contact_successfully(): void
    {
        $contactId = 10;
        $existing = new ContactEntity(new ContactType('phone'), '+111', sort: 0, isActive: false);
        $existing->id = $contactId;

        $this->contactRepo->shouldReceive('findById')
            ->with($contactId)
            ->once()
            ->andReturn($existing);

        $this->contactRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(function (ContactEntity $c) {
                return $c->type->getValue() === 'email'
                    && $c->value === 'new@example.com'
                    && $c->link === 'mailto:new@example.com'
                    && $c->iconUuid === 'icon'
                    && $c->caption === 'Caption'
                    && $c->analyticsField === 'main'
                    // isActive и sort не должны меняться при update
                    && $c->isActive === false
                    && $c->sort === 0;
            }))
            ->andReturn($existing);

        $dto = new ContactData(
            type: 'email',
            value: 'new@example.com',
            link: 'mailto:new@example.com',
            iconUuid: 'icon',
            caption: 'Caption',
            analyticsField: 'main',
        );

        $result = $this->useCase->execute($contactId, $dto, $this->mockUserPermission(edit: true));

        $this->assertSame($existing, $result);
        $this->assertSame('email', $result->type->getValue());
        $this->assertSame('new@example.com', $result->value);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new ContactData(type: 'phone', value: '123');

        $this->contactRepo->shouldNotReceive('findById');
        $this->contactRepo->shouldNotReceive('save');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(10, $dto, $this->mockUserPermission()); // edit: false
    }

    #[Test]
    public function throws_exception_when_contact_not_found(): void
    {
        $this->contactRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $dto = new ContactData(type: 'email', value: 'test@test.com');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Контакт не найден');
        $this->useCase->execute(999, $dto, $this->mockUserPermission(edit: true));
    }
}
