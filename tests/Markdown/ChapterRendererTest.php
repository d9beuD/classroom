<?php

namespace App\Tests\Markdown;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType;
use App\EntityEnum\ChapterSectionVariant;
use App\Markdown\ChapterRenderer;
use PHPUnit\Framework\TestCase;

final class ChapterRendererTest extends TestCase
{
    private function render(ChapterSection ...$sections): string
    {
        $chapter = new Chapter();
        foreach ($sections as $section) {
            $chapter->addSection($section);
        }

        return (new ChapterRenderer())->render($chapter);
    }

    private static function section(ChapterSectionType $type, string $content, int $position = 0): ChapterSection
    {
        return (new ChapterSection())->setType($type)->setContent($content)->setPosition($position);
    }

    public function testSectionsAreRenderedInPositionOrder(): void
    {
        $html = $this->render(
            self::section(ChapterSectionType::Paragraph, 'Second', 1),
            self::section(ChapterSectionType::Paragraph, 'First', 0),
        );

        $this->assertSame("<p>First</p>\n<p>Second</p>\n", $html);
    }

    public function testCodeWithoutFenceIsFencedWithItsLanguage(): void
    {
        $code = self::section(ChapterSectionType::Code, "<?php\necho \$a;")->setLanguage(ChapterSectionLanguage::Php);

        $this->assertSame(
            "<pre><code class=\"language-php\">&lt;?php\necho \$a;\n</code></pre>\n",
            $this->render($code),
        );
    }

    public function testBackticksInCodeDoNotCloseTheFence(): void
    {
        $html = $this->render(self::section(ChapterSectionType::Code, "Use ```php to start a block.\n````\nstill code"));

        $this->assertSame("<pre><code>Use ```php to start a block.\n````\nstill code\n</code></pre>\n", $html);
    }

    public function testCodeLooksLikeMarkdownIsNotInterpreted(): void
    {
        $code = self::section(ChapterSectionType::Code, '# Not a title')->setLanguage(ChapterSectionLanguage::Markdown);

        $this->assertSame("<pre><code class=\"language-markdown\"># Not a title\n</code></pre>\n", $this->render($code));
    }

    public function testMermaidIsFencedAsMermaid(): void
    {
        $this->assertSame(
            "<pre><code class=\"language-mermaid\">flowchart LR\n  A --&gt; B\n</code></pre>\n",
            $this->render(self::section(ChapterSectionType::Mermaid, "flowchart LR\n  A --> B")),
        );
    }

    public function testHeadingUsesItsLevelOnOneLine(): void
    {
        $heading = self::section(ChapterSectionType::Heading, "Mise en\nplace")->setLevel(3);

        $this->assertSame("<h3>Mise en place</h3>\n", $this->render($heading));
        $this->assertSame("<h2>Sans niveau</h2>\n", $this->render(self::section(ChapterSectionType::Heading, 'Sans niveau')));
    }

    public function testQuoteKeepsAllItsLines(): void
    {
        $this->assertSame(
            "<blockquote>\n<p>Ligne 1</p>\n<p>Ligne 2</p>\n</blockquote>\n",
            $this->render(self::section(ChapterSectionType::Quote, "Ligne 1\n\nLigne 2")),
        );
    }

    public function testCalloutIsWrappedWithItsVariant(): void
    {
        $callout = self::section(ChapterSectionType::Callout, 'Attention **ici**')->setVariant(ChapterSectionVariant::Warning);

        $this->assertSame("<div class=\"alert theme-warning\">\n<p>Attention <strong>ici</strong></p>\n</div>\n", $this->render($callout));
    }

    public function testRawHtmlAndUnsafeLinksAreNeutralised(): void
    {
        $script = $this->render(self::section(ChapterSectionType::Paragraph, '<script>alert(1)</script>'));
        $link = $this->render(self::section(ChapterSectionType::Paragraph, 'Un [lien](javascript:alert) piégé'));

        $this->assertStringNotContainsString('<script>', $script);
        $this->assertStringContainsString('<a', $link);
        $this->assertStringNotContainsString('href="javascript:', $link);
    }

    public function testEmptySectionsAreSkipped(): void
    {
        $this->assertSame('', $this->render(self::section(ChapterSectionType::Paragraph, "  \n ")));
    }
}
