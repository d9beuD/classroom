<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionType;
use App\EntityEnum\ChapterSectionVariant;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Callout extends ChapterSection
{
    /** Null means neutral. */
    #[ORM\Column(nullable: true, enumType: ChapterSectionVariant::class)]
    private ?ChapterSectionVariant $variant = null;

    public function getType(): ChapterSectionType
    {
        return ChapterSectionType::Callout;
    }

    public function getVariant(): ?ChapterSectionVariant
    {
        return $this->variant;
    }

    public function setVariant(?ChapterSectionVariant $variant): static
    {
        $this->variant = $variant;

        return $this;
    }
}
