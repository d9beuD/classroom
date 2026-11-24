<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * @return Course[] Returns an array of Course objects
     */
    public function findAllWithChapters(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.chapters', 'ch')
            ->orderBy('c.label', \SortDirection::Ascending)
            ->getQuery()
            ->getResult()
        ;
    }
}
