<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240826104233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feed_item (
          id INT AUTO_INCREMENT NOT NULL,
          feed_id INT NOT NULL,
          event_id INT DEFAULT NULL,
          feed_item_id VARCHAR(255) DEFAULT NULL,
          data JSON NOT NULL COMMENT \'(DC2Type:json)\',
          hash VARCHAR(255) NOT NULL,
          message LONGTEXT DEFAULT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          last_seen_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_9F8CCE4951A5BC03 (feed_id),
          UNIQUE INDEX UNIQ_9F8CCE4971F7E88B (event_id),
          UNIQUE INDEX feed_feedItemId_unique (feed_id, feed_item_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          feed_item
        ADD
          CONSTRAINT FK_9F8CCE4951A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id)');
        $this->addSql('ALTER TABLE
          feed_item
        ADD
          CONSTRAINT FK_9F8CCE4971F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('DROP INDEX feed_feedItemId_unique ON event');
        $this->addSql('ALTER TABLE event DROP hash');
        $this->addSql('ALTER TABLE feed ADD last_read_count INT DEFAULT NULL, ADD message LONGTEXT DEFAULT NULL, ADD sync_to_feed TINYINT(1) NOT NULL AFTER configuration');

        $this->addSql('INSERT INTO feed_item (feed_id, event_id, feed_item_id, data, hash, created_at, updated_at, last_seen_at)
          SELECT e.feed_id, e.id, e.feed_item_id, \'[]\', \'--\', e.created_at, e.updated_at, e.updated_at 
          FROM event e
          WHERE e.feed_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed_item DROP FOREIGN KEY FK_9F8CCE4951A5BC03');
        $this->addSql('ALTER TABLE feed_item DROP FOREIGN KEY FK_9F8CCE4971F7E88B');
        $this->addSql('DROP TABLE feed_item');
        $this->addSql('ALTER TABLE feed DROP last_read_count, DROP message, DROP sync_to_feed');
        $this->addSql('ALTER TABLE event ADD hash VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX feed_feedItemId_unique ON event (feed_id, feed_item_id)');
    }
}
