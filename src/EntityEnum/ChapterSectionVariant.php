<?php

namespace App\EntityEnum;

enum ChapterSectionVariant: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Principal',
            self::Secondary => 'Secondaire',
            self::Success => 'Succès',
            self::Danger => 'Danger',
            self::Warning => 'Avertissement',
            self::Info => 'Information',
        };
    }
}
