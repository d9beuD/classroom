<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityControllerTest extends WebTestCase
{
    public function testLoginGoesHomeEvenAfterBeingBouncedFromAProtectedPage(): void
    {
        $client = self::createClient();
        $client->request('GET', '/profile');
        $client->followRedirect();

        $client->submitForm('Se connecter', [
            'login[email]' => AppFixtures::STUDENT_EMAIL,
            'login[password]' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert.theme-success', 'vous êtes connecté');
    }

    public function testWrongPasswordShowsAnError(): void
    {
        $client = self::createClient();
        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'login[email]' => AppFixtures::STUDENT_EMAIL,
            'login[password]' => 'wrong',
        ]);

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert.theme-danger', 'Identifiants invalides.');
    }

    public function testLogoutShowsAMessage(): void
    {
        $client = self::createClient();
        $client->loginUser(self::getContainer()->get(UserRepository::class)->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]) ?? self::fail('Missing fixture user.'));

        $client->request('GET', '/logout');
        $client->followRedirect();

        self::assertSelectorTextContains('.alert.theme-success', 'Vous êtes déconnecté.');
    }
}
