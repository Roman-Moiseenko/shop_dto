<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\Public;
use App\Modules\Content\Application\Actions\Public\ViewPublicPageUseCase;
use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Application\Interfaces\PageRepositoryInterface;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
class ViewPublicPageUseCaseTest extends TestCase
{
    private PageRepositoryInterface $pageRepo;
    private ContentBlockRepositoryInterface $blockRepo;
    private ViewPublicPageUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageRepo = Mockery::mock(PageRepositoryInterface::class);
        $this->blockRepo = Mockery::mock(ContentBlockRepositoryInterface::class);
        $this->useCase = new ViewPublicPageUseCase($this->pageRepo, $this->blockRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createPublishedPage(): PageEntity
    {
        $page = new PageEntity('Home', new Slug('home'));
        $page->id = 1;
        $page->publish(); // статус published
        return $page;
    }

    #[Test]
    public function returns_page_and_blocks_when_published(): void
    {
        $slug = 'home';
        $page = $this->createPublishedPage();

        $this->pageRepo->shouldReceive('findBySlug')
            ->with(Mockery::on(fn(Slug $s) => (string)$s === $slug), false)
            ->once()
            ->andReturn($page);

        $expectedBlocks = [];
        $this->blockRepo->shouldReceive('listByContainer')
            ->with(Mockery::type(ContainerType::class), 1)
            ->once()
            ->andReturn($expectedBlocks);

        // execute возвращает PageEntity
        $resultPage = $this->useCase->execute($slug);
        $this->assertSame($page, $resultPage);

        $blocks = $this->useCase->getBlocks($resultPage);
        $this->assertSame($expectedBlocks, $blocks);
    }

    #[Test]
    public function returns_null_when_page_not_found(): void
    {
        $slug = 'nonexistent';
        $this->pageRepo->shouldReceive('findBySlug')
            ->with(Mockery::on(fn(Slug $s) => (string)$s === $slug), false)
            ->once()
            ->andReturn(null);

        $result = $this->useCase->execute($slug);
        $this->assertNull($result);
    }

    #[Test]
    public function returns_null_when_page_is_not_published(): void
    {
        $slug = 'draft-page';
        $page = new PageEntity('Draft', new Slug('draft-page'));
        $page->id = 2; // статус draft (не опубликована)

        $this->pageRepo->shouldReceive('findBySlug')
            ->with(Mockery::on(fn(Slug $s) => (string)$s === $slug), false)
            ->once()
            ->andReturn($page);

        $result = $this->useCase->execute($slug);
        $this->assertNull($result);
    }

    #[Test]
    public function returns_null_when_page_is_trashed(): void
    {
        $slug = 'trashed-page';
        $page = new PageEntity('Trashed', new Slug('trashed-page'));
        $page->id = 3;
        $page->publish();
        // имитируем удаление
        $page->deletedAt = new \DateTimeImmutable();

        $this->pageRepo->shouldReceive('findBySlug')
            ->with(Mockery::on(fn(Slug $s) => (string)$s === $slug), false)
            ->once()
            ->andReturn($page);

        $result = $this->useCase->execute($slug);
        $this->assertNull($result);
    }
}
