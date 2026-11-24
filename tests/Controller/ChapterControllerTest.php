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
}
