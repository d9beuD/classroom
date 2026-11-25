<?php

namespace App\Markdown;

use League\CommonMark\Node\Inline\AbstractInline;

/** A Lucide icon written as <i data-lucide="name"></i> in a section (see LucideIconSyntax). */
final class LucideIcon extends AbstractInline
{
    public function __construct(
        public readonly string $name,
    ) {
        parent::__construct();
    }
}
