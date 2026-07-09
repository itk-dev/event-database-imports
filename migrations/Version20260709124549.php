<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709124549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add convert_newlines_to_br column to feed';
    }

    public function up(Schema $schema): void
    {
        // NOT NULL without a default: MariaDB backfills existing feed rows with the
        // implicit 0 (false), so existing feeds keep their current behaviour.
        $this->addSql('ALTER TABLE feed ADD convert_newlines_to_br TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feed DROP convert_newlines_to_br');
    }
}
