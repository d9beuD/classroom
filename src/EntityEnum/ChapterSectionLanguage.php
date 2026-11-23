<?php

namespace App\EntityEnum;

/**
 * Language of a code section. Values are Monaco Editor language ids.
 */
enum ChapterSectionLanguage: string
{
    case Php = 'php';
    case Twig = 'twig';
    case Html = 'html';
    case Css = 'css';
    case Javascript = 'javascript';
    case Typescript = 'typescript';
    case Python = 'python';
    case Java = 'java';
    case C = 'c';
    case Cpp = 'cpp';
    case Csharp = 'csharp';
    case Sql = 'sql';
    case Shell = 'shell';
    case Json = 'json';
    case Yaml = 'yaml';
    case Xml = 'xml';
    case Markdown = 'markdown';

    public function label(): string
    {
        return match ($this) {
            self::Php => 'PHP',
            self::Twig => 'Twig',
            self::Html => 'HTML',
            self::Css => 'CSS',
            self::Javascript => 'JavaScript',
            self::Typescript => 'TypeScript',
            self::Python => 'Python',
            self::Java => 'Java',
            self::C => 'C',
            self::Cpp => 'C++',
            self::Csharp => 'C#',
            self::Sql => 'SQL',
            self::Shell => 'Shell',
            self::Json => 'JSON',
            self::Yaml => 'YAML',
            self::Xml => 'XML',
            self::Markdown => 'Markdown',
        };
    }
}
