<?php

namespace App\Tests\Command;

use App\DataFixtures\AppFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AddUserRolesCommandTest extends KernelTestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->tester = new CommandTester((new Application(self::bootKernel()))->find('app:user:add-roles'));
    }

    public function testRolesAreAddedToTheUser(): void
    {
        // An inert role, so other tests sharing the database still see a plain student.
        $this->assertSame(0, $this->tester->execute(['email' => AppFixtures::STUDENT_EMAIL, 'roles' => ['role_beta', 'ROLE_STUDENT']]));

        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => AppFixtures::STUDENT_EMAIL]);
        $this->assertEqualsCanonicalizing(['ROLE_STUDENT', 'ROLE_BETA', 'ROLE_USER'], $user->getRoles());
    }

    public function testUnknownEmailFails(): void
    {
        $this->assertSame(1, $this->tester->execute(['email' => 'nobody@example.com', 'roles' => ['ROLE_ADMIN']]));
        $this->assertStringContainsString('No user found', $this->tester->getDisplay());
    }

    public function testRoleWithoutPrefixIsRejected(): void
    {
        $this->assertSame(1, $this->tester->execute(['email' => AppFixtures::STUDENT_EMAIL, 'roles' => ['admin']]));
        $this->assertStringContainsString('ADMIN', $this->tester->getDisplay());
    }
}
