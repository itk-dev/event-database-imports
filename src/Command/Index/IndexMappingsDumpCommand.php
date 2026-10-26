<?php

namespace App\Command\Index;

use App\Model\Indexing\IndexNames;
use App\Model\Indexing\Mappings\MappingsProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Export the Elasticsearch mapping (schema only, no settings) of every index to
 * committed JSON files, so the index contract is reviewable and can be consumed
 * by the read-only `event-database-api` project. A CI check keeps the committed
 * files in sync with the PHP mapping classes (see .github/workflows/index-mappings.yaml).
 */
#[AsCommand(
    name: 'app:index:mappings:dump',
    description: 'Dump Elasticsearch index mappings (schema only) to JSON files',
)]
final class IndexMappingsDumpCommand extends Command
{
    private const string DEFAULT_PATH = './resources/mappings';

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Directory to write mapping files to', self::DEFAULT_PATH);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = rtrim((string) $input->getOption('path'), '/');
        $filesystem = new Filesystem();
        $filesystem->mkdir($path);

        foreach (IndexNames::cases() as $index) {
            $file = sprintf('%s/%s.json', $path, $index->value);
            $json = json_encode(
                MappingsProvider::mappingFor($index),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
            $filesystem->dumpFile($file, $json."\n");
            $io->writeln(sprintf('Wrote mapping for <info>%s</info> to %s', $index->value, $file));
        }

        $io->success(sprintf('Dumped %d index mappings to %s', count(IndexNames::cases()), $path));

        return Command::SUCCESS;
    }
}
