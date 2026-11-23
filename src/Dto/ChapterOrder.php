<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * New order of a course's chapters: every chapter id of the course, first to last.
 */
final readonly class ChapterOrder
{
    /**
     * @param list<int> $ids
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Unique]
        #[Assert\All([new Assert\Type('integer')])]
        public array $ids = [],
    ) {
    }
}
