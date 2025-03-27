<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326151430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert HTML character entities in excerpt fields to their actual characters';
    }

    public function up(Schema $schema): void
    {
        // Get all records that have excerpt fields containing '&' (indicating HTML entities)
        $qb = $this->connection->createQueryBuilder();
        $records = $qb->select('e.id', 'e.excerpt')
            ->from('event', 'e')
            ->where($qb->expr()->like('e.excerpt', ':pattern'))
            ->setParameter('pattern', '%&%')
            ->executeQuery()
            ->fetchAllAssociative();

        // Process each record
        foreach ($records as $record) {
            if (null === $record['excerpt']) {
                continue;  // Skip if excerpt is null
            }

            // Decode HTML entities using PHP's native function
            $decodedExcerpt = html_entity_decode($record['excerpt'], ENT_QUOTES | ENT_HTML5);

            // Only update if something changed
            if ($decodedExcerpt !== $record['excerpt']) {
                $this->connection->createQueryBuilder()
                    ->update('event', 'e')
                    ->set('e.excerpt', ':excerpt')
                    ->where('e.id = :id')
                    ->setParameter('excerpt', $decodedExcerpt)
                    ->setParameter('id', $record['id'])
                    ->executeStatement();
            }
        }
    }

    public function down(Schema $schema): void
    {
        // No need to revert this change as it just formats data without functional changes
    }
}
