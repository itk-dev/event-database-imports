<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630080112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD feed_id INT DEFAULT NULL, ADD feed_item_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA751A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA751A5BC03 ON event (feed_id)');
        $this->addSql('ALTER TABLE feed ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044ABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_234044ABA76ED395 ON feed (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA751A5BC03');
        $this->addSql('DROP INDEX IDX_3BAE0AA751A5BC03 ON event');
        $this->addSql('ALTER TABLE event DROP feed_id, DROP feed_item_id');
        $this->addSql('ALTER TABLE feed DROP FOREIGN KEY FK_234044ABA76ED395');
        $this->addSql('DROP INDEX IDX_234044ABA76ED395 ON feed');
        $this->addSql('ALTER TABLE feed DROP user_id');
    }
}
