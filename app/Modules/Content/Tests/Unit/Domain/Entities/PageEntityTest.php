<?php

namespace App\Modules\Content\Tests\Unit\Domain\Entities;
use App\Modules\Content\Domain\Entities\PageEntity;
use App\Modules\Content\Domain\ValueObjects\ContentType;
use App\Modules\Content\Domain\ValueObjects\Meta;
use App\Modules\Content\Domain\ValueObjects\PageStatus;
use App\Modules\Content\Domain\ValueObjects\PageTemplate;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
class PageEntityTest extends TestCase
{
    #[Test] public function creates_page_with_minimum_required_fields(): void
    {
        $slug = new Slug('test-page');
        $page = new PageEntity('Test Page', $slug);

        $this->assertNull($page->id);
        $this->assertSame('test-page', (string)$page->slug);
        $this->assertSame('Test Page', $page->title);
        $this->assertNull($page->content);
        $this->assertTrue($page->status->isDraft());
        $this->assertNull($page->publishedAt);
        $this->assertNull($page->meta);
        $this->assertTrue($page->contentType->isSimple());
        $this->assertNull($page->template);
        $this->assertNull($page->createdAt);
        $this->assertNull($page->updatedAt);
    }

    #[Test] public function creates_page_with_optional_fields(): void
    {
        $meta = new Meta(['title' => 'SEO Title']);
        $template = new PageTemplate('landing');
        $page = new PageEntity(
            'Landing',
            new Slug('landing-page'),
            contentType: ContentType::widgetBased(),
            content: '<p>Hello</p>',
            status: PageStatus::published(),
            meta: $meta,
            authorId: 123,
            template: $template,
        );

        $this->assertTrue($page->isPublished());
        $this->assertNotNull($page->publishedAt);
        $this->assertSame('SEO Title', $page->meta->getTitle());
        $this->assertTrue($page->isWidgetBased());
        $this->assertSame(123, $page->getAuthorId());
        $this->assertSame('landing', (string)$page->template);
    }

    #[Test] public function publish_sets_status_and_date(): void
    {
        $page = new PageEntity('Publish', new Slug('publish'));
        $fixedDate = new DateTimeImmutable('2026-01-01 12:00:00');
        $page->publish($fixedDate);

        $this->assertTrue($page->isPublished());
        $this->assertSame($fixedDate, $page->publishedAt);
    }

    #[Test] public function unpublish_resets_to_draft(): void
    {
        $page = new PageEntity('Unpublish', new Slug('unpublish'), status: PageStatus::published());
        $page->unpublish();

        $this->assertTrue($page->status->isDraft());
        $this->assertNull($page->publishedAt);
    }

    #[Test] public function sets_and_gets_blocks(): void
    {
        $page = new PageEntity('Blocks', new Slug('blocks'));
        $blocks = ['block1', 'block2'];
        $page->setBlocks($blocks);

        $this->assertSame($blocks, $page->getBlocks());
    }

    #[Test] public function sets_author_id(): void
    {
        $page = new PageEntity('Author', new Slug('author'));
        $page->setAuthorId(456);
        $this->assertSame(456, $page->getAuthorId());
    }

    #[Test] public function sets_timestamps(): void
    {
        $page = new PageEntity('Time', new Slug('time'));
        $created = new DateTimeImmutable('2025-01-01');
        $updated = new DateTimeImmutable('2025-02-01');
        $page->setCreatedAt($created);
        $page->setUpdatedAt($updated);

        $this->assertSame($created, $page->createdAt);
        $this->assertSame($updated, $page->updatedAt);
    }
}
