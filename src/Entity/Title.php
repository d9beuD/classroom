<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Title extends ChapterSection
{
    /** Heading level, 2 to 6. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $level = null;

    public function getType(): ChapterSectionType
    {
        return ChapterSectionType::Heading;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): static
    {
        $this->level = $level;

        return $this;
    }
}
