<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const PROFESSOR_EMAIL = 'professor@example.com';
    public const STUDENT_EMAIL = 'student@example.com';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            [self::PROFESSOR_EMAIL, 'Professeur Test', ['ROLE_PROFESSOR']],
            [self::STUDENT_EMAIL, 'Élève Test', ['ROLE_STUDENT']],
        ] as [$email, $name, $roles]) {
            $user = (new User())->setEmail($email)->setName($name)->setRoles($roles);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
