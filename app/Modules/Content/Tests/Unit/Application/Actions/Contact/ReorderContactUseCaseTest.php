<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Contact;

use App\Modules\Content\Application\Actions\Contact\ReorderContactUseCase;
use App\Modules\Content\Application\DTOs\Contact\ReorderContactData;
use App\Modules\Content\Application\Interfaces\ContactRepositoryInterface;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ReorderContactUseCaseTest extends TestCase
{
    use MockPermission;

    private ContactRepositoryInterface $contactRepo;
    private ReorderContactUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->contactRepo = Mockery::mock(ContactRepositoryInterface::class);
        $this->useCase = new ReorderContactUseCase($this->contactRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function reorders_contact_successfully(): void
    {
        $contactId = 42;
        $newSort = 3;
        $dto = new ReorderContactData(id: $contactId, newSort: $newSort);

        $this->contactRepo->shouldReceive('updateSortOrder')
            ->once()
            ->with($contactId, $newSort);

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        // нет исключений — успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new ReorderContactData(id: 1, newSort: 2);

        $this->contactRepo->shouldNotReceive('updateSortOrder');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // edit: false
    }
}
