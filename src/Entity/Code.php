<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Code extends ChapterSection
{
    /** Null means plain text. */
    #[ORM\Column(nullable: true, enumType: ChapterSectionLanguage::class)]
    private ?ChapterSectionLanguage $language = null;

    public function getType(): ChapterSectionType
    {
        return ChapterSectionType::Code;
    }

    public function getLanguage(): ?ChapterSectionLanguage
    {
        return $this->language;
    }

    public function setLanguage(?ChapterSectionLanguage $language): static
    {
        $this->language = $language;

        return $this;
    }
}
