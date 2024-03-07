<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305113148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_organization (event_id INT NOT NULL, organization_id INT NOT NULL, INDEX IDX_2CFD698F71F7E88B (event_id), INDEX IDX_2CFD698F32C8A3DE (organization_id), PRIMARY KEY(event_id, organization_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_organization ADD CONSTRAINT FK_2CFD698F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_organization ADD CONSTRAINT FK_2CFD698F32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1EE637C5E237E06 ON organization (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9099C97B5E237E06 ON vocabulary (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_organization DROP FOREIGN KEY FK_2CFD698F71F7E88B');
        $this->addSql('ALTER TABLE event_organization DROP FOREIGN KEY FK_2CFD698F32C8A3DE');
        $this->addSql('DROP TABLE event_organization');
        $this->addSql('DROP INDEX UNIQ_C1EE637C5E237E06 ON organization');
        $this->addSql('DROP INDEX UNIQ_9099C97B5E237E06 ON vocabulary');
        $this->addSql('DROP INDEX UNIQ_389B7835E237E06 ON tag');
    }
}
