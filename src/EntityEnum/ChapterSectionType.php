<?php

namespace App\EntityEnum;

enum ChapterSectionType: string
{
    case Paragraph = 'paragraph';
    case Heading = 'heading';
    case Quote = 'quote';
    case Code = 'code';
    case Mermaid = 'mermaid';
    case Table = 'table';
    case Callout = 'callout';

    public function label(): string
    {
        return match ($this) {
            self::Paragraph => 'Paragraphe',
            self::Heading => 'Titre',
            self::Quote => 'Citation',
            self::Code => 'Code',
            self::Mermaid => 'Diagramme Mermaid',
            self::Table => 'Tableau',
            self::Callout => 'Encadré',
        };
    }
}
