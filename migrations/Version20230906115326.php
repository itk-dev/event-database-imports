<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906115326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed ADD organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044AB32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('CREATE INDEX IDX_234044AB32C8A3DE ON feed (organization_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feed DROP FOREIGN KEY FK_234044AB32C8A3DE');
        $this->addSql('DROP INDEX IDX_234044AB32C8A3DE ON feed');
        $this->addSql('ALTER TABLE feed DROP organization_id');
    }
}
