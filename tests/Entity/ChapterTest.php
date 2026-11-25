<?php

namespace App\Tests\Entity;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\Entity\Content;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PHPUnit\Framework\TestCase;

final class ChapterTest extends TestCase
{
    public function testUpdatedAtFallsBackToCreationDate(): void
    {
        $chapter = new Chapter();

        $this->assertSame($chapter->getCreatedAt(), $chapter->getUpdatedAt());
    }

    public function testUpdatedAtIsTheLatestSectionChange(): void
    {
        $chapter = new Chapter();
        $old = new Content();
        $recent = new Content();
        $chapter->addSection($old)->addSection($recent);

        usleep(1000);
        $recent->touch($this->preUpdate($recent, ['content']));

        $this->assertSame($recent->getUpdatedAt(), $chapter->getUpdatedAt());
        $this->assertGreaterThan($old->getUpdatedAt(), $chapter->getUpdatedAt());
    }

    public function testFillingTheRenderCacheIsNotAnEdit(): void
    {
        $section = new Content();
        $before = $section->getUpdatedAt();

        usleep(1000);
        $section->touch($this->preUpdate($section, ['renderedHash', 'renderedContent']));

        $this->assertSame($before, $section->getUpdatedAt());
    }

    /**
     * @param list<string> $fields changed fields
     */
    private function preUpdate(ChapterSection $section, array $fields): PreUpdateEventArgs
    {
        $changeSet = array_fill_keys($fields, ['old', 'new']);

        return new PreUpdateEventArgs($section, $this->createStub(EntityManagerInterface::class), $changeSet);
    }
}
