<?php

namespace App\Twig\Components;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class CoursesMenu
{
    public function __construct(
        protected CourseRepository $courseRepository,
    ) {
    }

    /**
     * @return Course[]
     */
    public function getCourses(): array
    {
        return $this->courseRepository->findAllWithChapters();
    }
}
