<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
class Chapter
{
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
    #[ORM\OneToMany(targetEntity: ChapterSection::class, mappedBy: 'chapter', orphanRemoval: true)]
    private Collection $sections;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column]
    private ?int $position = null;

    public function __construct()
    {
        $this->feedbacks = new ArrayCollection();
        $this->sections = new ArrayCollection();
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
}
