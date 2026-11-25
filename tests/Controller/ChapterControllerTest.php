<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Code;
use App\EntityEnum\ChapterSectionLanguage;
use App\Repository\ChapterSectionRepository;
use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use App\Tests\Storage\CourseImageStorageTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ChapterControllerTest extends WebTestCase
{
    public function testEditPageListsCourseImagesWithTheirMarkdown(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));
        $course = self::getContainer()->get(CourseRepository::class)->findOneBy(['slug' => 'html']) ?? self::fail('Missing fixture course.');
        $directory = CourseImageStorageTest::storeImage($course, 'Arbre.png', '0123456789abcdef');

        try {
            $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');

            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('.course-images h2', 'Images du cours');
            self::assertSelectorExists('.course-images input[type=file]');
            self::assertSelectorExists('dialog#course-images-drawer.drawer-end .course-images');
            self::assertSelectorExists('button[data-bs-toggle=drawer][data-bs-target="#course-images-drawer"]');
            self::assertSelectorExists(\sprintf('.course-images [data-clipboard-text-param="![arbre](/uploads/courses/%d/arbre-01234567.png)"]', $course->getId()));
        } finally {
            new Filesystem()->remove($directory.'/arbre-01234567.png');
        }
    }

    public function testNewPageOffersTheCourseImagesDrawer(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/html/chapter/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('dialog#course-images-drawer .course-images input[type=file]');
        self::assertSelectorExists('button[data-bs-toggle=drawer][data-bs-target="#course-images-drawer"]');
    }

    public function testInvalidCsrfTokenIsShownOnTheEditForm(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');
        $client->submitForm('Update', ['chapter[_token]' => 'invalid']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('form[name=chapter] .alert', 'CSRF');
    }

    public function testHeadingLevelCanBeChanged(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');
        self::assertSelectorTextSame('#chapter_sections_0_level_combobox .combobox-value', 'Niveau 2');
        self::assertSelectorExists('#chapter_sections_0_level_combobox [data-bs-value="3"]');

        $client->submitForm('Update', ['chapter[sections][0][level]' => '3']);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertAnySelectorTextSame('h3', 'Les débuts du Web');
    }

    public function testInvalidHeadingLevelShowsAnError(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');
        $client->submitForm('Update', ['chapter[sections][0][level]' => '1']);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('#chapter_sections_0_level_error1');
    }

    public function testAddedSectionIsSavedAsItsTypeSubclass(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $crawler = $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');
        $form = $crawler->selectButton('Update')->form();
        $values = $form->getPhpValues();
        $values['chapter']['sections'][99] = ['content' => 'echo 1;', 'type' => 'code', 'language' => 'php', 'position' => 99];
        $client->request('POST', $form->getUri(), $values);
        self::assertResponseRedirects();

        $section = self::getContainer()->get(ChapterSectionRepository::class)->findOneBy(['content' => 'echo 1;']);
        $this->assertInstanceOf(Code::class, $section);
        $this->assertSame(ChapterSectionLanguage::Php, $section->getLanguage());
    }

    public function testAddedSectionWithUnknownTypeIsRejected(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $crawler = $client->request('GET', '/course/html/chapter/histoire-et-contexte/edit');
        $form = $crawler->selectButton('Update')->form();
        $values = $form->getPhpValues();
        $values['chapter']['sections'][99] = ['content' => '| a |', 'type' => 'table', 'position' => 99];
        $client->request('POST', $form->getUri(), $values);

        self::assertResponseStatusCodeSame(400);
    }
}
