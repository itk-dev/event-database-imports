<?php

namespace App\Command;

use App\Repository\EventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:event:delete',
    description: 'Delete an event. CAn be used to clean-up feed events as these cannot be deleted through EasyAdmin',
)]
class EventDeleteCommand extends Command
{
    public function __construct(
        private readonly EventRepository $eventRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('event-id', InputArgument::REQUIRED, 'ID of the event to delete')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $eventId = $input->getArgument('event-id');

        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            $io->error(sprintf('Event with ID %s not found', $eventId));

            return Command::FAILURE;
        }

        $eventTitle = $event->getTitle() ?? 'UNKNOWN TITLE';
        $question = sprintf('Delete event with ID %s: %s?', $eventId, $eventTitle);

        if ($io->confirm($question, false)) {
            try {
                $this->eventRepository->remove($event, true);

                $io->success(sprintf('Event with ID %s: %s deleted', $eventId, $eventTitle));

                return Command::SUCCESS;
            } catch (\Throwable $e) {
                $io->error(sprintf('Error deleting event with ID %s: %s', $eventId, $e->getMessage()));

                return Command::FAILURE;
            }
        } else {
            $io->info('Event not deleted');

            return Command::INVALID;
        }
    }
}
