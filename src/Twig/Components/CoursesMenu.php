<?php

namespace App\Twig\Components;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final readonly class CoursesMenu
{
    public function __construct(
        private CourseRepository $courseRepository,
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
