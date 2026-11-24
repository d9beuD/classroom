<?php

namespace App\Entity;

use App\EntityInterface\SluggableInterface;
use App\EntityTrait\SluggableTrait;
use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SortDirection;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter implements SluggableInterface
{
    use SluggableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $excerpt = null;

    /**
     * @var Collection<int, ChapterFeedback>
     */
    #[ORM\OneToMany(targetEntity: ChapterFeedback::class, mappedBy: 'chapter', orphanRemoval: true)]
    private Collection $feedbacks;

    /**
     * @var Collection<int, ChapterSection>
     */
    #[ORM\OneToMany(targetEntity: ChapterSection::class, mappedBy: 'chapter', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => SortDirection::Ascending])]
    private Collection $sections;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column]
    private ?int $position = null;

    /** HTML of all the sections, rendered when the chapter is saved (see ChapterRenderer). */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $renderedContent = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** Null for chapters created before authors were recorded. */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $author = null;

    public function __construct()
    {
        $this->feedbacks = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    /**
     * @return Collection<int, ChapterFeedback>
     */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(ChapterFeedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setChapter($this);
        }

        return $this;
    }

    public function removeFeedback(ChapterFeedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getChapter() === $this) {
                $feedback->setChapter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChapterSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(ChapterSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setChapter($this);
        }

        return $this;
    }

    public function removeSection(ChapterSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getChapter() === $this) {
                $section->setChapter(null);
            }
        }

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

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

    public function getRenderedContent(): ?string
    {
        return $this->renderedContent;
    }

    public function setRenderedContent(?string $renderedContent): static
    {
        $this->renderedContent = $renderedContent;

        return $this;
    }

    public function getSluggableText(): string
    {
        return (string) $this->title;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Most recent change to one of the sections, or the creation date when there is none.
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return max([$this->createdAt, ...$this->sections->map(fn (ChapterSection $section): \DateTimeImmutable => $section->getUpdatedAt())->toArray()]);
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
