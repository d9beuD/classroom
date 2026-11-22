<?php

namespace App\EntityListener;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(Events::prePersist, entity: Course::class)]
#[AsEntityListener(Events::preUpdate, entity: Course::class)]
class CourseEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Course $course, PrePersistEventArgs $event): void
    {
        $course->computeSlug($this->slugger, $course->getSluggableText());
    }

    public function preUpdate(Course $course, PreUpdateEventArgs $event): void
    {
        $course->computeSlug($this->slugger, $course->getSluggableText());
    }
}
