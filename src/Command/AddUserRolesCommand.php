<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:add-roles',
    description: 'Adds one or more roles to a user',
)]
final readonly class AddUserRolesCommand
{
    public function __construct(
        private UserRepository $users,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<string> $roles
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('The email of the user')] string $email,
        #[Argument('The roles to add (e.g. ROLE_PROFESSOR)')] array $roles,
    ): int {
        if (!$user = $this->users->findOneBy(['email' => $email])) {
            $io->error(\sprintf('No user found with the email "%s".', $email));

            return Command::FAILURE;
        }

        $roles = array_map(strtoupper(...), $roles);
        if ($invalid = array_filter($roles, static fn (string $role): bool => !str_starts_with($role, 'ROLE_'))) {
            $io->error(\sprintf('Roles must start with "ROLE_": %s.', implode(', ', $invalid)));

            return Command::FAILURE;
        }

        $user->setRoles(array_values(array_unique([...$user->getRoles(), ...$roles])));
        $this->entityManager->flush();

        $io->success(\sprintf('%s now has the roles: %s.', $email, implode(', ', $user->getRoles())));

        return Command::SUCCESS;
    }
}
