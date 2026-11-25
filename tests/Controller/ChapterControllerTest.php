<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Code;
use App\EntityEnum\ChapterSectionLanguage;
use App\Repository\ChapterSectionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ChapterControllerTest extends WebTestCase
{
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
        self::assertInstanceOf(Code::class, $section);
        self::assertSame(ChapterSectionLanguage::Php, $section->getLanguage());
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
