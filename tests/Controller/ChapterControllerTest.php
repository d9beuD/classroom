<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
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
}
