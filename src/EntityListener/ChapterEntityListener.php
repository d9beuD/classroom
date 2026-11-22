<?php

namespace App\EntityListener;

use App\Entity\Chapter;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(Events::prePersist, entity: Chapter::class)]
#[AsEntityListener(Events::preUpdate, entity: Chapter::class)]
class ChapterEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Chapter $chapter, PrePersistEventArgs $event): void
    {
        $chapter->computeSlug($this->slugger, $chapter->getSluggableText());
    }

    public function preUpdate(Chapter $chapter, PreUpdateEventArgs $event): void
    {
        $chapter->computeSlug($this->slugger, $chapter->getSluggableText());
    }
}
