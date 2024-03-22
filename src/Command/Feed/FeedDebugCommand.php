<?php

namespace App\Command\Feed;

use App\Repository\FeedRepository;
use App\Service\Feeds\Mapper\FeedConfigurationMapper;
use App\Service\Feeds\Mapper\FeedMapperInterface;
use App\Service\Feeds\Parser\FeedParserInterface;
use App\Service\TagsNormalizer;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * THIS COMMAND IS ONLY HERE DURING DEVELOPMENT FOR FASTER FEED PARSING TEST/DEVELOPMENT.
 */
#[AsCommand(
    name: 'app:feed:debug',
    description: 'Try parsing feed and output raw data',
)]
final class FeedDebugCommand extends Command
{
    private const int DEFAULT_OPTION = -1;

    public function __construct(
        private readonly FeedParserInterface $feedParser,
        private readonly FeedMapperInterface $feedMapper,
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
        private readonly TagsNormalizer $tagsNormalizer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('feed-id', '', InputOption::VALUE_REQUIRED, 'Limit imports to the feed ID given', self::DEFAULT_OPTION)
            ->addOption('limit', '', InputOption::VALUE_REQUIRED, 'Limit the number of items parsed pr. feed', self::DEFAULT_OPTION);
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = (int) $input->getOption('feed-id');
        $limit = (int) $input->getOption('limit');

        // @todo: Convert config array to value object.
        $feed = $this->feedRepository->findOneBy(['id' => $feedId]);
        if (is_null($feed)) {
            $io->error('Feed not found with the provided id');

            return Command::FAILURE;
        }
        if (!$feed->isEnabled()) {
            $io->error(sprintf('The feed "%s" is disabled', $feed->getName() ?? 'unknown'));

            return Command::FAILURE;
        }

        $index = 0;
        $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
        foreach ($this->feedParser->parse($feed, $config->url, $config->rootPointer) as $item) {
            // What should happen. Send item into queue system and in the next step map and validate data. But right
            // here for debugging we by-pass message system and try mapping the item.
            $feedItem = $this->feedMapper->getFeedItemFromArray($item, $config);
            $feedItem->feedId = $feedId;
            $feedItem->tags = $this->tagsNormalizer->normalize($feedItem->tags);
            $io->definitionList(
                ['Id' => $feedItem->id],
                ['Title' => $feedItem->title],
                ['Excerpt' => wordwrap($feedItem->excerpt ?? '', 80)],
                ['Url' => $feedItem->url],
                ['Tags' => implode(', ', $feedItem->tags)],
            );

            ++$index;
            if ($limit > 0 && $index >= $limit) {
                break;
            }
        }

        $io->success('Feed debugging completed.');

        return Command::SUCCESS;
    }
}
