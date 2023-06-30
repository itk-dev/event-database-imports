<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230630084226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE daily_occurrence (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, occurrence_id INT NOT NULL, start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ticket_price_range VARCHAR(255) NOT NULL, room VARCHAR(255) DEFAULT NULL, INDEX IDX_431E7C9071F7E88B (event_id), INDEX IDX_431E7C9030572FAC (occurrence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occurrence (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, start DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ticket_price_range VARCHAR(255) NOT NULL, room VARCHAR(255) DEFAULT NULL, INDEX IDX_BEFD81F371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9071F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9030572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id)');
        $this->addSql('ALTER TABLE occurrence ADD CONSTRAINT FK_BEFD81F371F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daily_occurrence DROP FOREIGN KEY FK_431E7C9071F7E88B');
        $this->addSql('ALTER TABLE daily_occurrence DROP FOREIGN KEY FK_431E7C9030572FAC');
        $this->addSql('ALTER TABLE occurrence DROP FOREIGN KEY FK_BEFD81F371F7E88B');
        $this->addSql('DROP TABLE daily_occurrence');
        $this->addSql('DROP TABLE occurrence');
    }
}
