<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\IndexContactsUseCase;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContactEntity;
use App\Modules\Content\Domain\ValueObjects\ContactType;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class IndexContactsUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private IndexContactsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new IndexContactsUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_all_contacts_when_view_permission_granted(): void
    {
        $contacts = [
            new ContactEntity(new ContactType('phone'), '123', sort: 0),
            new ContactEntity(new ContactType('email'), 'test@test.com', sort: 1),
        ];

        $this->contactRepo->shouldReceive('all')
            ->once()
            ->andReturn($contacts);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));
        $this->assertSame($contacts, $result);
    }

    #[Test]
    public function throws_access_denied_when_view_permission_absent(): void
    {
        $this->contactRepo->shouldNotReceive('all');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
