<?php

namespace App\Markdown;

use League\CommonMark\Node\Node;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * Lets a section show a Lucide icon with exactly <i data-lucide="name"></i>, while any other raw HTML stays escaped.
 *
 * Registered above HtmlInlineParser so it claims the tag first; inside a code span the tag stays text.
 */
final class LucideIconSyntax implements InlineParserInterface, NodeRendererInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex('<i data-lucide="([a-z0-9-]+)"><\/i>')->caseSensitive();
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $inlineContext->getCursor()->advanceBy($inlineContext->getFullMatchLength());
        $inlineContext->getContainer()->appendChild(new LucideIcon($inlineContext->getSubMatches()[0]));

        return true;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        \assert($node instanceof LucideIcon);

        // The name matched [a-z0-9-]+, so it needs no escaping.
        return \sprintf('<i data-lucide="%s" aria-hidden="true"></i>', $node->name);
    }
}
