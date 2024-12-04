<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Repository\FeedRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:event:delete',
    description: 'Delete event(s). Can be used to clean-up feed events as these cannot be deleted through EasyAdmin',
)]
class EventDeleteCommand extends Command
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly FeedRepository $feedRepository,
        private CacheManager $imageCacheManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('event-id', null, InputOption::VALUE_REQUIRED, 'ID of the event to delete')
            ->addOption('feed-id', null, InputOption::VALUE_REQUIRED, 'ID of the feed to delete all events from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $eventId = $input->getOption('event-id');
        $feedId = $input->getOption('feed-id');

        if (null !== $eventId && null !== $feedId) {
            $io->warning('Event ID and Feed ID are not allowed simultaneously');

            return Command::INVALID;
        }

        if (null === $eventId && null === $feedId) {
            $io->warning('You must use either the Event ID or Feed ID option');

            return Command::INVALID;
        }

        if (null !== $eventId) {
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

        if (null !== $feedId) {
            $feed = $this->feedRepository->find($feedId);
            if (!$feed) {
                $io->error(sprintf('Feed with ID %s not found', $feedId));

                return Command::FAILURE;
            }

            $name = $feed->getName() ?? 'UNKNOWN';
            $question = sprintf('Delete ALL events from feed  %s: %s?', $feedId, $name);

            if ($io->confirm($question, false)) {
                try {
                    $events = $feed->getEvents();
                    $count = count($events);

                    $progressBar = new ProgressBar($output, $count);
                    $progressBar->start();
                    $progressBar->setMessage(sprintf('Deleting events from feed  %s: %s?', $feedId, $name));
                    $progressBar->display();

                    foreach ($events as $event) {
                        $image = $event->getImage()?->getLocal();
                        if (null !== $image) {
                            $this->imageCacheManager->remove($image);
                        }

                        $this->eventRepository->remove($event, true);

                        $progressBar->advance();
                    }

                    $progressBar->finish();

                    return Command::SUCCESS;
                } catch (\Throwable $e) {
                    $io->error(sprintf('Error deleting events with ID %s: %s', $feedId, $e->getMessage()));

                    return Command::FAILURE;
                }
            } else {
                $io->info('Events not deleted');

                return Command::INVALID;
            }
        }
    }
}
