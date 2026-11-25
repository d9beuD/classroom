<?php

namespace App\Tests\Markdown;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType;
use App\EntityEnum\ChapterSectionVariant;
use App\Markdown\ChapterRenderer;
use App\Markdown\RendererClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ChapterRendererTest extends TestCase
{
    /**
     * Requests sent to the renderer service: [path, decoded JSON payload].
     *
     * @var list<array{string, mixed}>
     */
    private array $requests = [];

    /**
     * @param list<ChapterSection>                                                       $sections
     * @param (callable(string $path, array<string, mixed> $payload): MockResponse)|null $service  answers of the renderer service; null: it is down
     */
    private function render(array $sections, ?callable $service = null, bool $force = false): string
    {
        $chapter = new Chapter();
        foreach ($sections as $section) {
            $chapter->addSection($section);
        }

        return $this->renderer($service)->render($chapter, $force);
    }

    /**
     * @param (callable(string $path, array<string, mixed> $payload): MockResponse)|null $service
     */
    private function renderer(?callable $service): ChapterRenderer
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use ($service): MockResponse {
            $path = (string) parse_url($url, \PHP_URL_PATH);
            $payload = json_decode($options['body'], true);
            $this->requests[] = [$path, $payload];

            return $service ? $service($path, $payload) : new MockResponse('', ['http_code' => 503]);
        });

        return new ChapterRenderer(new RendererClient($httpClient, 'http://renderer:3000', new NullLogger()));
    }

    // A method, as PHPStan would narrow $this->requests after a first assertion on it.
    private function requestCount(): int
    {
        return \count($this->requests);
    }

    private function section(ChapterSectionType $type, string $content, int $position = 0): ChapterSection
    {
        return new ChapterSection()->setType($type)->setContent($content)->setPosition($position);
    }

    public function testSectionsAreRenderedInPositionOrder(): void
    {
        $html = $this->render([
            $this->section(ChapterSectionType::Paragraph, 'Second', 1),
            $this->section(ChapterSectionType::Paragraph, 'First', 0),
        ]);

        $this->assertSame("<p>First</p>\n<p>Second</p>\n", $html);
    }

    public function testCodeFallsBackToAFencedBlockWithItsLanguage(): void
    {
        $code = $this->section(ChapterSectionType::Code, "<?php\necho \$a;")->setLanguage(ChapterSectionLanguage::Php);

        $this->assertSame(
            "<pre><code class=\"language-php\">&lt;?php\necho \$a;\n</code></pre>\n",
            $this->render([$code]),
        );
    }

    public function testBackticksInCodeDoNotCloseTheFence(): void
    {
        $html = $this->render([$this->section(ChapterSectionType::Code, "Use ```php to start a block.\n````\nstill code")]);

        $this->assertSame("<pre><code>Use ```php to start a block.\n````\nstill code\n</code></pre>\n", $html);
    }

    public function testCodeLooksLikeMarkdownIsNotInterpreted(): void
    {
        $code = $this->section(ChapterSectionType::Code, '# Not a title')->setLanguage(ChapterSectionLanguage::Markdown);

        $this->assertSame("<pre><code class=\"language-markdown\"># Not a title\n</code></pre>\n", $this->render([$code]));
    }

    public function testMermaidFallsBackToAFencedBlock(): void
    {
        $this->assertSame(
            "<pre><code class=\"language-mermaid\">flowchart LR\n  A --&gt; B\n</code></pre>\n",
            $this->render([$this->section(ChapterSectionType::Mermaid, "flowchart LR\n  A --> B")]),
        );
    }

    public function testHeadingUsesItsLevelOnOneLine(): void
    {
        $heading = $this->section(ChapterSectionType::Heading, "Mise en\nplace")->setLevel(3);

        $this->assertSame("<h3>Mise en place</h3>\n", $this->render([$heading]));
        $this->assertSame("<h2>Sans niveau</h2>\n", $this->render([$this->section(ChapterSectionType::Heading, 'Sans niveau')]));
    }

    public function testQuoteKeepsAllItsLines(): void
    {
        $this->assertSame(
            "<blockquote>\n<p>Ligne 1</p>\n<p>Ligne 2</p>\n</blockquote>\n",
            $this->render([$this->section(ChapterSectionType::Quote, "Ligne 1\n\nLigne 2")]),
        );
    }

    public function testCalloutIsWrappedWithItsVariant(): void
    {
        $callout = $this->section(ChapterSectionType::Callout, 'Attention **ici**')->setVariant(ChapterSectionVariant::Warning);

        $this->assertSame("<div class=\"alert theme-warning\">\n<p>Attention <strong>ici</strong></p>\n</div>\n", $this->render([$callout]));
    }

    public function testRawHtmlAndUnsafeLinksAreNeutralised(): void
    {
        $script = $this->render([$this->section(ChapterSectionType::Paragraph, '<script>alert(1)</script>')]);
        $link = $this->render([$this->section(ChapterSectionType::Paragraph, 'Un [lien](javascript:alert) piégé')]);

        $this->assertStringNotContainsString('<script>', $script);
        $this->assertStringContainsString('<a', $link);
        $this->assertStringNotContainsString('href="javascript:', $link);
    }

    public function testEmptySectionsAreSkipped(): void
    {
        $this->assertSame('', $this->render([$this->section(ChapterSectionType::Paragraph, "  \n ")]));
    }

    public function testCodeIsHighlightedByTheRendererService(): void
    {
        $code = $this->section(ChapterSectionType::Code, "echo \$a;\n")->setLanguage(ChapterSectionLanguage::Php);

        $html = $this->render([$code], fn (): MockResponse => new JsonMockResponse(['html' => '<pre class="shiki">php</pre>']));

        $this->assertSame('<pre class="shiki">php</pre>', $html);
        $this->assertSame([['/highlight', ['code' => 'echo $a;', 'lang' => 'php']]], $this->requests);
    }

    public function testCodeWithoutLanguageIsHighlightedAsPlainText(): void
    {
        $this->render([$this->section(ChapterSectionType::Code, 'x')], fn (): MockResponse => new JsonMockResponse(['html' => '']));

        $this->assertSame([['/highlight', ['code' => 'x', 'lang' => '']]], $this->requests);
    }

    public function testDiagramsAreRenderedInBothThemesWithUniqueSvgIds(): void
    {
        $html = $this->render(
            [
                $this->section(ChapterSectionType::Mermaid, 'flowchart LR', 0),
                $this->section(ChapterSectionType::Mermaid, 'pie', 1),
            ],
            fn (string $path, array $payload): MockResponse => new JsonMockResponse(['svg' => '<svg id="'.$payload['svgId'].'" theme="'.$payload['theme'].'"/>']),
        );

        $figure = function (string $code): string {
            $id = 'mermaid-'.substr(hash('xxh128', $code), 0, 12);

            return "<figure class=\"mermaid-diagram\">\n"
                ."<div class=\"mermaid-light\"><svg id=\"$id-light\" theme=\"light\"/></div>\n"
                ."<div class=\"mermaid-dark\"><svg id=\"$id-dark\" theme=\"dark\"/></div>\n"
                ."</figure>\n";
        };
        $this->assertSame($figure('flowchart LR').$figure('pie'), $html);
    }

    public function testUnchangedDiagramIsNotRenderedAgain(): void
    {
        $diagram = $this->section(ChapterSectionType::Mermaid, 'pie');
        $service = fn (): MockResponse => new JsonMockResponse(['svg' => '<svg/>']);

        $first = $this->render([$diagram], $service);
        $second = $this->render([$diagram], $service);

        $this->assertSame($first, $second);
        $this->assertSame(2, $this->requestCount(), 'Only the first render calls the service (light + dark).');

        $diagram->setContent('flowchart LR');
        $this->render([$diagram], $service);
        $this->assertSame(4, $this->requestCount(), 'Changed code is rendered again.');

        $this->render([$diagram], $service, force: true);
        $this->assertSame(6, $this->requestCount(), 'A forced render ignores the stored diagram.');
    }

    public function testFailedDiagramIsNotStored(): void
    {
        $diagram = $this->section(ChapterSectionType::Mermaid, 'pie');

        $this->render([$diagram]);
        $this->assertNull($diagram->getRenderedHash());

        $html = $this->render([$diagram], fn (): MockResponse => new JsonMockResponse(['svg' => '<svg/>']));
        $this->assertStringContainsString('<figure class="mermaid-diagram">', $html);
    }

    public function testDiagramFallsBackToItsCodeWhenTheDarkRenderingFails(): void
    {
        $html = $this->render(
            [$this->section(ChapterSectionType::Mermaid, 'pie')],
            fn (string $path, array $payload): MockResponse => 'dark' === $payload['theme']
                ? new MockResponse('', ['http_code' => 503])
                : new JsonMockResponse(['svg' => '<svg/>']),
        );

        $this->assertSame("<pre><code class=\"language-mermaid\">pie\n</code></pre>\n", $html);
    }

    public function testInvalidDiagramFallsBackToItsCode(): void
    {
        $html = $this->render(
            [$this->section(ChapterSectionType::Mermaid, 'flowchart LR\n  A -->')],
            fn (): MockResponse => new JsonMockResponse(['error' => 'Parse error'], ['http_code' => 422]),
        );

        $this->assertSame("<pre><code class=\"language-mermaid\">flowchart LR\\n  A --&gt;\n</code></pre>\n", $html);
    }

    public function testOtherSectionsDoNotCallTheRendererService(): void
    {
        $this->render([$this->section(ChapterSectionType::Paragraph, 'Texte')]);

        $this->assertSame([], $this->requests);
    }
}
