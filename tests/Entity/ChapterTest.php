<?php

namespace App\Tests\Entity;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
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
        $old = new ChapterSection();
        $recent = new ChapterSection();
        $chapter->addSection($old)->addSection($recent);

        usleep(1000);
        $recent->touch();

        $this->assertSame($recent->getUpdatedAt(), $chapter->getUpdatedAt());
        $this->assertGreaterThan($old->getUpdatedAt(), $chapter->getUpdatedAt());
    }
}
