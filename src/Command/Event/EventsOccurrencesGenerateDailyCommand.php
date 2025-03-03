<?php

namespace App\Command\Event;

use App\Entity\Event;
use App\Message\DailyOccurrenceMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'events:occurrences:generate-daily',
    description: 'Generate and update all daily occurrences',
)]
class EventsOccurrencesGenerateDailyCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e.id')->from(Event::class, 'e');

        $query = $qb->getQuery();

        foreach ($query->toIterable() as $row) {
            $message = new DailyOccurrenceMessage($row['id']);
            $this->messageBus->dispatch($message);
        }

        $io->success('DailyOccurrence messages dispatched for all events.');

        return Command::SUCCESS;
    }
}
