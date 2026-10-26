<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708095754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align schema with DBAL 4: map json columns to native JSON and drop the (DC2Type:...) datetime comments.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_occurrence CHANGE start start DATETIME NOT NULL, CHANGE end end DATETIME NOT NULL');
        $this->addSql('ALTER TABLE feed CHANGE configuration configuration JSON NOT NULL, CHANGE last_read last_read DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE feed_item CHANGE data data JSON NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE last_seen_at last_seen_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE occurrence CHANGE start start DATETIME NOT NULL, CHANGE end end DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE email_verified_at email_verified_at DATETIME DEFAULT NULL, CHANGE terms_accepted_at terms_accepted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_occurrence CHANGE start start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE end end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE feed CHANGE configuration configuration JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE last_read last_read DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE feed_item CHANGE data data JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_seen_at last_seen_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE occurrence CHANGE start start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE end end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE email_verified_at email_verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE terms_accepted_at terms_accepted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
