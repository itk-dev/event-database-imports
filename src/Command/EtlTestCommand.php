<?php

namespace App\Command;

use App\Services\Etl\Transformers\DataTimeTransformer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Json;
use Wizaplace\Etl\Loaders\MemoryLoader;

#[AsCommand(
    name: 'app:etl:test',
    description: 'Test library transformations',
)]
class EtlTestCommand extends Command
{
    public function __construct(
        private readonly Etl $etl,
        private readonly Json $jsonExtractor,
        private readonly MemoryLoader $memoryLoader,
        private readonly DataTimeTransformer $dataTimeTransformer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $feed = 'http://train.dk/calenderjsonrewrite.php';

        $options = [
            Json::COLUMNS => [
                'id' => '$.events..id',
                'name' => '$.events..name',
                'description' => '$.events..description',
                'start' => '$.events..starttime',
                'end' => '$.events..endtime',
            ],
        ];

        $this->etl
            ->extract(
                $this->jsonExtractor,
                $feed,
                $options
            )
            ->transform($this->dataTimeTransformer, [DataTimeTransformer::COLUMNS => ['start', 'end']])
            ->load($this->memoryLoader, '', [MemoryLoader::INDEX => 'id'])
            ->run();

        $row = $this->memoryLoader->get(10061);
        $data = $row->toArray();
        dump($data);

        return Command::SUCCESS;
    }
}
