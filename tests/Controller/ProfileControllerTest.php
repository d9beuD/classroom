<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    public function testAnonymousVisitorIsSentToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        self::assertResponseRedirects('/login');
    }

    public function testUserChangesTheirName(): void
    {
        $client = static::createClient();
        $users = static::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]));

        $client->request('GET', '/profile');
        $client->submitForm('Enregistrer', ['profile[name]' => 'Ada Lovelace']);

        self::assertResponseRedirects('/profile');
        $client->followRedirect();
        self::assertSelectorTextContains('[role=status]', 'Profil enregistré.');
        self::assertSame('Ada Lovelace', $users->findOneBy(['email' => AppFixtures::STUDENT_EMAIL])->getName());
    }

    public function testEmptyNameIsRefused(): void
    {
        $client = static::createClient();
        $users = static::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]));

        $client->request('GET', '/profile');
        $client->submitForm('Enregistrer', ['profile[name]' => '']);

        self::assertResponseIsUnprocessable();
        self::assertSame('Professeur Test', $users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL])->getName());
    }
}
