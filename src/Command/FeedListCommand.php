<?php

namespace App\Command;

use App\Repository\FeedRepository;
use App\Services\Feeds\Mapper\FeedConfigurationMapper;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:feed:list',
    description: 'List feeds from the database',
)]
final class FeedListCommand extends Command
{
    public function __construct(
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // @todo: Add option to limit based on organization (data available in other PR currently).
        $this->addOption('show-disabled', '', InputOption::VALUE_NONE, 'Also list disabled feeds');
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $showDisabled = $input->getOption('show-disabled');

        if ($showDisabled) {
            $feeds = $this->feedRepository->findAll();
        } else {
            $feeds = $this->feedRepository->findBy(['enabled' => !$showDisabled]);
        }

        foreach ($feeds as $feed) {
            $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
            $io->definitionList(
                ['Id' => $feed->getId()],
                ['Name' => $feed->getName()],
                ['Url' => $config->url]
            );
        }

        return Command::SUCCESS;
    }
}
