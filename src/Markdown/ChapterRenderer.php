<?php

namespace App\Markdown;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionType;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders a chapter's sections, in position order, to the HTML stored on the chapter.
 *
 * Each section is one Markdown block. Code and Mermaid sections hold raw code (no ``` fence),
 * so they get one here; headings get their # prefix from the section level, quotes their > prefix,
 * and callouts a wrapper div coloured by their variant. Raw HTML written in a section is escaped.
 */
final class ChapterRenderer
{
    private readonly MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function render(Chapter $chapter): string
    {
        $sections = $chapter->getSections()->toArray();
        usort($sections, fn (ChapterSection $a, ChapterSection $b): int => $a->getPosition() <=> $b->getPosition());

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
            ChapterSectionType::Code => $this->markdown($this->fence($content, $section->getLanguage()?->value ?? '')),
            ChapterSectionType::Mermaid => $this->markdown($this->fence($content, 'mermaid')),
            ChapterSectionType::Callout => \sprintf(
                "<div class=\"alert%s\">\n%s</div>\n",
                $section->getVariant() ? ' theme-'.$section->getVariant()->value : '',
                $this->markdown($content),
            ),
            default => $this->markdown($content),
        };
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
