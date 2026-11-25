<?php

namespace App\EntityEnum;

use App\Entity\Callout;
use App\Entity\ChapterSection;
use App\Entity\Code;
use App\Entity\Content;
use App\Entity\Mermaid;
use App\Entity\Quote;
use App\Entity\Title;

/** A section's type. The values are the discriminator of ChapterSection's subclasses. */
enum ChapterSectionType: string
{
    case Paragraph = 'paragraph';
    case Heading = 'heading';
    case Quote = 'quote';
    case Code = 'code';
    case Mermaid = 'mermaid';
    case Callout = 'callout';

    public function label(): string
    {
        return match ($this) {
            self::Paragraph => 'Paragraphe',
            self::Heading => 'Titre',
            self::Quote => 'Citation',
            self::Code => 'Code',
            self::Mermaid => 'Diagramme Mermaid',
            self::Callout => 'Encadré',
        };
    }

    public function newSection(): ChapterSection
    {
        return match ($this) {
            self::Paragraph => new Content(),
            self::Heading => new Title(),
            self::Quote => new Quote(),
            self::Code => new Code(),
            self::Mermaid => new Mermaid(),
            self::Callout => new Callout(),
        };
    }
}
