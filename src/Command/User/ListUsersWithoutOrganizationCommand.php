<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use App\Types\UserRoles;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:list-without-organization',
    description: 'List users below editor role who have no organization'
)]
final class ListUsersWithoutOrganizationCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $editorOrAbove = [
            UserRoles::ROLE_EDITOR->value,
            UserRoles::ROLE_ADMIN->value,
            UserRoles::ROLE_SUPER_ADMIN->value,
        ];

        $qb = $this->userRepository->createQueryBuilder('u')
            ->leftJoin('u.organizations', 'o')
            ->where('o.id IS NULL');

        // Roles are stored as a JSON array in the database, so we use `LIKE` to checks if a role is in the array.
        foreach ($editorOrAbove as $i => $role) {
            $qb->andWhere("u.roles NOT LIKE :role{$i}")
                ->setParameter("role{$i}", "%\"{$role}\"%");
        }

        $users = $qb->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();

        if (0 === \count($users)) {
            $io->success('No users found matching the criteria.');

            return Command::SUCCESS;
        }

        $rows = array_map(fn ($user): array => [
            $user->getId(),
            $user->getName(),
            $user->getMail(),
            implode(', ', $user->getRoles()),
            $user->isEnabled() ? 'Yes' : 'No',
        ], $users);

        $io->table(
            ['ID', 'Name', 'Email', 'Roles', 'Enabled'],
            $rows,
        );

        $io->note(sprintf('Found %d user(s) without an organization who are not at least editor.', \count($users)));

        return Command::SUCCESS;
    }
}
