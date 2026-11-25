<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Mermaid extends ChapterSection
{
    /** xxh128 hash of the code that renderedContent was rendered from (see ChapterRenderer). */
    #[ORM\Column(length: 32, nullable: true)]
    private ?string $renderedHash = null;

    /** The rendered diagram figure, reused while the code hash is unchanged. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $renderedContent = null;

    public function getType(): ChapterSectionType
    {
        return ChapterSectionType::Mermaid;
    }

    public function getRenderedHash(): ?string
    {
        return $this->renderedHash;
    }

    public function getRenderedContent(): ?string
    {
        return $this->renderedContent;
    }

    public function setRendered(?string $hash, ?string $content): static
    {
        $this->renderedHash = $hash;
        $this->renderedContent = $content;

        return $this;
    }
}
