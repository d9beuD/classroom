<?php

namespace App\Markdown;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionType;
use App\EntityEnum\ChapterSectionVariant;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders a chapter's sections, in position order, to the HTML stored on the chapter.
 *
 * Each section is one Markdown block; headings get their # prefix from the section level, quotes
 * their > prefix, and callouts a wrapper div coloured by their variant. Raw HTML written in a
 * section is escaped.
 *
 * Code and Mermaid sections hold raw code (no ``` fence). They are rendered by the renderer
 * service (Shiki highlighting, Mermaid SVG in a light and a dark theme); if it fails, they fall back to a fenced code block.
 */
final class ChapterRenderer
{
    private readonly MarkdownConverter $converter;

    /** Whether the current render ignores the diagrams stored on the sections. */
    private bool $force = false;

    public function __construct(
        private readonly RendererClient $renderer,
    ) {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * @param bool $force render diagrams again even when their code is unchanged (e.g. after a renderer change)
     */
    public function render(Chapter $chapter, bool $force = false): string
    {
        $sections = $chapter->getSections()->toArray();
        usort($sections, fn (ChapterSection $a, ChapterSection $b): int => $a->getPosition() <=> $b->getPosition());
        $this->force = $force;

        return implode('', array_map($this->renderSection(...), $sections));
    }

    private function renderSection(ChapterSection $section): string
    {
        $content = str_replace("\r\n", "\n", $section->getContent() ?? '');
        if ('' === trim($content)) {
            return '';
        }

        return match ($section->getType()) {
            // One line: a heading can't span several.
            ChapterSectionType::Heading => $this->markdown(str_repeat('#', $section->getLevel() ?? 2).' '.preg_replace('/\s+/', ' ', trim($content))),
            ChapterSectionType::Quote => $this->markdown('> '.str_replace("\n", "\n> ", $content)),
            ChapterSectionType::Code => $this->code($content, $section->getLanguage()->value ?? ''),
            ChapterSectionType::Mermaid => $this->diagram($section, $content),
            ChapterSectionType::Callout => \sprintf(
                "<div class=\"alert%s\">\n%s</div>\n",
                $section->getVariant() instanceof ChapterSectionVariant ? ' theme-'.$section->getVariant()->value : '',
                $this->markdown($content),
            ),
            default => $this->markdown($content),
        };
    }

    private function code(string $code, string $language): string
    {
        return $this->renderer->highlight(rtrim($code, "\n"), $language)
            ?? $this->markdown($this->fence($code, $language));
    }

    /**
     * A light and a dark rendering, one of which app.css hides, as Mermaid writes its colours in the SVG.
     *
     * The figure is stored on the section with the hash of its code, and reused while the code is unchanged.
     * A failed rendering isn't stored, so the next save tries again.
     */
    private function diagram(ChapterSection $section, string $code): string
    {
        $hash = hash('xxh128', $code);
        if (!$this->force && $hash === $section->getRenderedHash() && null !== $section->getRenderedContent()) {
            return $section->getRenderedContent();
        }

        // Derived from the code, not the diagram's rank, so adding or moving a diagram leaves the others' stored SVG valid.
        // shortcut: two identical diagrams in one chapter share an SVG id (same styles, so no visible effect); dedupe if that ever matters.
        $id = 'mermaid-'.substr($hash, 0, 12);
        $light = $this->renderer->mermaid($code, $id.'-light');
        $dark = null !== $light ? $this->renderer->mermaid($code, $id.'-dark', 'dark') : null;
        if (null === $dark) {
            return $this->markdown($this->fence($code, 'mermaid'));
        }

        $figure = "<figure class=\"mermaid-diagram\">\n<div class=\"mermaid-light\">".$light."</div>\n<div class=\"mermaid-dark\">".$dark."</div>\n</figure>\n";
        $section->setRendered($hash, $figure);

        return $figure;
    }

    /**
     * Wraps code in a fenced block, with a fence longer than any run of backticks in the code
     * so that the code can't close it early.
     */
    private function fence(string $code, string $info): string
    {
        preg_match_all('/`+/', $code, $runs);
        $longestRun = max([0, ...array_map(strlen(...), $runs[0])]);
        $fence = str_repeat('`', max(3, $longestRun + 1));

        return $fence.$info."\n".rtrim($code, "\n")."\n".$fence;
    }

    private function markdown(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
