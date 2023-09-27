<?php

namespace App\Command;

use App\Message\FeedItemDataMessage;
use App\Repository\FeedRepository;
use App\Service\Feeds\Mapper\FeedConfigurationMapper;
use App\Service\Feeds\Parser\FeedParserInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(
    name: 'app:feed:import',
    description: 'Parse feed and import events from it',
)]
final class FeedImportCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FeedParserInterface $feedParser,
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('feedId', InputArgument::REQUIRED, 'Database feed id')
            ->addOption('limit', '', InputOption::VALUE_REQUIRED, 'Limit the number of items outputted', -1);
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = (int) $input->getArgument('feedId');
        $limit = $input->getOption('limit');

        $feed = $this->feedRepository->findOneBy(['id' => $feedId]);
        if (is_null($feed)) {
            $io->error(sprintf('Invalid feed id: %d', $feedId));

            return Command::INVALID;
        }
        if (!$feed->isEnabled()) {
            $io->error(sprintf('The feed "%s" is disabled', $feed->getName() ?? 'unknown'));

            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('Memory:%memory% [%bar%] Time:%elapsed%, Items:%current%');

        $index = 0;
        $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
        foreach ($this->feedParser->parse($feed, $config->url, $config->rootPointer) as $item) {
            $message = new FeedItemDataMessage($feedId, $config, $item);
            try {
                $this->messageBus->dispatch($message);
            } catch (TransportException|\LogicException) {
                // Ensure that message get into failed queue if connection to AMQP fails.
                $this->messageBus->dispatch($message, [new TransportNamesStamp('failed')]);
            }

            $progressBar->advance();

            ++$index;
            if ($limit > 0 && $index >= $limit) {
                break;
            }
        }

        $feed->setLastRead(new \DateTimeImmutable());
        $this->feedRepository->save($feed, true);

        $progressBar->finish();
        $io->success('Feed import completed.');

        return Command::SUCCESS;
    }
}
