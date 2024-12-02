<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:change-password',
    description: 'Change user password'
)]
class ChangePasswordCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');

        /** @var ?User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $email]);
        if (null === $user) {
            throw new InvalidArgumentException(sprintf('User %s does not exist', $email));
        }

        $password = $io->askHidden('New Password?', function (string $password): string {
            if (empty($password)) {
                throw new \RuntimeException('Password cannot be empty.');
            }

            return $password;
        });

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();

        $io->success(sprintf('Password for user %s updated', $user->getMail()));

        return Command::SUCCESS;
    }
}
