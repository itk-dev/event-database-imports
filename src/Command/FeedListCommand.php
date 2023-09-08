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
    private const SELECTIONS = ['enabled', 'disabled', 'all'];

    public function __construct(
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // @todo: Add option to limit based on organization (data available in other PR currently).
        $this->addOption('status', '', InputOption::VALUE_REQUIRED, sprintf('Show the current feed status (Values: %s)', implode(', ', self::SELECTIONS)), 'enabled');
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $status = $input->getOption('status');
        if (!in_array($status, self::SELECTIONS)) {
            $io->error(sprintf('Invalid status: %s', $status));

            // Show how to run this command.
            // https://symfony.com/doc/current/console/calling_commands.html
            $this->getApplication()->doRun(new ArrayInput([
                'command' => $this->getName(),
                '--help'  => true,
            ]), $output);

            return Command::INVALID;
        }

        $feeds = match ($status) {
            'all' => $this->feedRepository->findAll(),
            'enabled' => $this->feedRepository->findBy(['enabled' => true]),
            'disabled' => $this->feedRepository->findBy(['enabled' => false])
        };

        foreach ($feeds as $feed) {
            $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
            $io->definitionList(
                ['Id' => $feed->getId()],
                ['Name' => $feed->getName()],
                ['Url' => $config->url],
                ['Enabled' => $feed->isEnabled() ? 'true' : 'false']
            );
        }

        return Command::SUCCESS;
    }
}
