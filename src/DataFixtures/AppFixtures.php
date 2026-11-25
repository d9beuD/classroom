<?php

namespace App\DataFixtures;

use App\Entity\Callout;
use App\Entity\Chapter;
use App\Entity\Code;
use App\Entity\Content;
use App\Entity\Course;
use App\Entity\Mermaid;
use App\Entity\Quote;
use App\Entity\Title;
use App\Entity\User;
use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionVariant;
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
            // One section of each type; the title stays first (ChapterControllerTest edits sections[0]).
            ->addSection(new Title()->setLevel(2)->setContent('Les débuts du Web')->setPosition(0))
            ->addSection(new Content()->setContent('Le HTML naît au CERN en 1991.')->setPosition(1))
            ->addSection(new Quote()->setContent('Le Web est plus une création sociale que technique.')->setPosition(2))
            ->addSection(new Code()->setLanguage(ChapterSectionLanguage::Html)->setContent('<p>Bonjour</p>')->setPosition(3))
            ->addSection(new Mermaid()->setContent("flowchart LR\n  SGML --> HTML")->setPosition(4))
            ->addSection(new Callout()->setVariant(ChapterSectionVariant::Info)->setContent('HTML n’est pas un langage de programmation.')->setPosition(5)));

        $manager->flush();
    }
}
