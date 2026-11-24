<?php

namespace App\DataFixtures;

use App\Entity\Chapter;
use App\Entity\ChapterSection;
use App\Entity\Course;
use App\Entity\User;
use App\EntityEnum\ChapterSectionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const PROFESSOR_EMAIL = 'professor@example.com';
    public const STUDENT_EMAIL = 'student@example.com';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            [self::PROFESSOR_EMAIL, 'Professeur Test', ['ROLE_PROFESSOR']],
            [self::STUDENT_EMAIL, 'Élève Test', ['ROLE_STUDENT']],
        ] as [$email, $name, $roles]) {
            $user = new User()->setEmail($email)->setName($name)->setRoles($roles);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }

        $course = new Course()->setLabel('HTML')->setIcon('code')->setIconColor('#e34f26')->setIconColorAlt('#ffffff');
        $manager->persist($course);
        $manager->persist(new Chapter()->setTitle('Histoire et contexte')->setExcerpt('Les origines du HTML.')->setCourse($course)->setPosition(0)
            ->addSection(new ChapterSection()->setType(ChapterSectionType::Heading)->setLevel(2)->setContent('Les débuts du Web')->setPosition(0)));

        $manager->flush();
    }
}
