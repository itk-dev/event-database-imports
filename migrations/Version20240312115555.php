<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240312115555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE public public_access TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX feed_feedItemId_unique ON event (feed_id, feed_item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX feed_feedItemId_unique ON event');
        $this->addSql('ALTER TABLE event CHANGE public_access public TINYINT(1) NOT NULL');
    }
}
