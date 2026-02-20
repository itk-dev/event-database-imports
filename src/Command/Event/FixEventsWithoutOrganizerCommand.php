<?php

namespace App\Command\Event;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:event:fix-without-organizer',
    description: 'Set organization on events without organizer based on the creating user\'s organization'
)]
final class FixEventsWithoutOrganizerCommand extends Command
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be changed without persisting');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Running in dry-run mode. No changes will be persisted.');
        }

        $events = $this->eventRepository->findBy(['organization' => null]);

        if (0 === \count($events)) {
            $io->success('No events found without an organization.');

            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d event(s) without an organization.', \count($events)));

        $fixed = 0;
        $skipped = [];

        foreach ($events as $event) {
            $createdBy = $event->getCreatedBy();

            if ('' === $createdBy) {
                $skipped[] = [$event->getId(), $event->getTitle(), 'No created by user'];
                continue;
            }

            $user = $this->userRepository->findOneBy(['mail' => $createdBy]);

            if (null === $user) {
                $skipped[] = [$event->getId(), $event->getTitle(), sprintf('User "%s" not found', $createdBy)];
                continue;
            }

            $organizations = $user->getOrganizations();

            if (1 !== $organizations->count()) {
                $skipped[] = [$event->getId(), $event->getTitle(), sprintf('User "%s" has %d organizations', $createdBy, $organizations->count())];
                continue;
            }

            $organization = $organizations->first();
            $event->setOrganization($organization);
            ++$fixed;

            $io->text(sprintf('Event #%d "%s" → Organization "%s"', $event->getId(), $event->getTitle(), $organization));
        }

        if (\count($skipped) > 0) {
            $io->section('Skipped events');
            $io->table(['ID', 'Title', 'Reason'], $skipped);
        }

        if ($fixed > 0 && !$dryRun) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('%s %d event(s).', $dryRun ? 'Would fix' : 'Fixed', $fixed));

        return Command::SUCCESS;
    }
}
