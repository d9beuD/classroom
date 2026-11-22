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
}
