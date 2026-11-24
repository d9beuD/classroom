<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType;
use App\EntityEnum\ChapterSectionVariant;
use App\Repository\ChapterSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapterSectionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ChapterSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chapter $chapter = null;

    #[ORM\Column(enumType: ChapterSectionType::class)]
    private ?ChapterSectionType $type = null;

    #[ORM\Column(enumType: ChapterSectionVariant::class, nullable: true)]
    private ?ChapterSectionVariant $variant = null;

    #[ORM\Column(enumType: ChapterSectionLanguage::class, nullable: true)]
    private ?ChapterSectionLanguage $language = null;

    /** Heading level (2 to 6) of a heading section; null for other types. */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $level = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(?Chapter $chapter): static
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getType(): ?ChapterSectionType
    {
        return $this->type;
    }

    public function setType(ChapterSectionType $type): static
    {
        $this->type = $type;

        return $this;
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

    public function getLanguage(): ?ChapterSectionLanguage
    {
        return $this->language;
    }

    public function setLanguage(?ChapterSectionLanguage $language): static
    {
        $this->language = $language;

        return $this;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Only fires when one of the section's own columns changed, so untouched sections keep their date.
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
