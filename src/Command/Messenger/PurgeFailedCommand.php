<?php

namespace App\Command\Messenger;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:messenger:purge-failed',
    description: 'Purge failed messages from database',
)]
#[AsCronTask(expression: '22 2 * * *', schedule: 'default')]
class PurgeFailedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('since', 's', InputOption::VALUE_REQUIRED, 'Purge failed messages older than X days', '15')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $since = intval($input->getOption('since'));

        if ($since <= 0) {
            $io->error('--since must be a positive integer');

            return Command::FAILURE;
        }

        try {
            $purged = $this->purgeFailed($since);

            $io->success(sprintf('Purged %d failed messages older than %d days', $purged, $since));

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $io->error('Error: '.$exception->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @throws Exception
     */
    private function purgeFailed(int $since): int
    {
        $connection = $this->entityManager->getConnection();
        $sql = 'DELETE FROM messenger_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $since, \PDO::PARAM_INT);

        return $stmt->executeQuery()->rowCount();
    }
}
