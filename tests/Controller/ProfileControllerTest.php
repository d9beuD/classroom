<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    public function testAnonymousVisitorIsSentToLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/profile');

        self::assertResponseRedirects('/login');
    }

    public function testUserChangesTheirName(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/profile');
        $client->submitForm('Enregistrer', ['profile[name]' => 'Ada Lovelace']);

        self::assertResponseRedirects('/profile');
        $client->followRedirect();
        self::assertSelectorTextContains('[role=status]', 'Profil enregistré.');
        $this->assertSame('Ada Lovelace', ($users->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]) ?? self::fail('Missing fixture user.'))->getName());
    }

    public function testEmptyNameIsRefused(): void
    {
        $client = self::createClient();
        $users = self::getContainer()->get(UserRepository::class);
        $client->loginUser($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/profile');
        $client->submitForm('Enregistrer', ['profile[name]' => '']);

        self::assertResponseIsUnprocessable();
        $this->assertSame('Professeur Test', ($users->findOneBy(['email' => AppFixtures::PROFESSOR_EMAIL]) ?? self::fail('Missing fixture user.'))->getName());
    }
}
