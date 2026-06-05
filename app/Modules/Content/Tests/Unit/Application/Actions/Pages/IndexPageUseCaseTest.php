<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Pages;

use App\Modules\Content\Application\Actions\Pages\IndexPageUseCase;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class IndexPageUseCaseTest extends TestCase
{
    use MockPermission;

    private PageRepositoryInterface $pageRepo;
    private IndexPageUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->useCase = new IndexPageUseCase($this->pageRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_paginator_when_view_permission_granted(): void
    {
        $paginator = Mockery::mock(\Illuminate\Pagination\LengthAwarePaginator::class);
        $this->pageRepo->shouldReceive('paginate')
            ->with(15)
            ->once()
            ->andReturn($paginator);

        $result = $this->useCase->execute($this->mockUserPermission(view: true));
        $this->assertSame($paginator, $result);
    }

    #[Test]
    public function throws_access_denied_when_missing_view_permission(): void
    {
        $this->pageRepo->shouldNotReceive('paginate');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($this->mockUserPermission()); // view: false
    }
}
