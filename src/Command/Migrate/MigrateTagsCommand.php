<?php

namespace App\Command\Migrate;

use App\Factory\TagsFactory;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:migrate:tags',
    description: 'Migrate tags from Legacy Event DB',
)]
class MigrateTagsCommand extends Command
{
    private const string RECEIVING_VOCABULARY = 'aarhusguiden';
    private const string LEGACY_API = 'https://api.detskeriaarhus.dk';
    private const string TAGS_ENDPOINT = '/api/tags';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly TagsFactory $tagsFactory,
        private readonly VocabularyRepository $vocabularyRepository,
        private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $vocabulary = $this->vocabularyRepository->findOneBy(['name' => self::RECEIVING_VOCABULARY]);

        if (null === $vocabulary) {
            $io->error(sprintf('Receiving tag vocabulary "%s" not found in database', self::RECEIVING_VOCABULARY));

            return Command::FAILURE;
        }

        $view = null;
        $created = 0;

        do {
            try {
                $path = $view?->{'hydra:next'} ?? self::TAGS_ENDPOINT;
                $url = self::LEGACY_API.$path;

                $response = $this->client->request('GET', $url, ['headers' => ['accept' => 'application/ld+json']]);
                $content = $response->getContent();
                $decoded = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
                $view = $decoded->{'hydra:view'};

                // First run only
                if (self::TAGS_ENDPOINT === $path) {
                    $io->progressStart($decoded->{'hydra:totalItems'});
                }

                foreach ($decoded->{'hydra:member'} as $member) {
                    foreach ($this->tagsFactory->createOrLookup([$member->name], $vocabulary) as $tag) {
                        ++$created;
                    }
                    $io->progressAdvance();
                }
            } catch (TransportExceptionInterface|\JsonException|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        } while (isset($view->{'hydra:next'}));

        $this->entityManager->flush();

        $io->progressFinish();

        $io->success(sprintf('%s tags migrated', $created));

        return Command::SUCCESS;
    }
}
