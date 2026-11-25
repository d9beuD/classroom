<?php

namespace App\Entity;

use App\EntityEnum\ChapterSectionType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Content extends ChapterSection
{
    public function getType(): ChapterSectionType
    {
        return ChapterSectionType::Content;
    }
}
