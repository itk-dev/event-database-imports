<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121093444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag RENAME INDEX uniq_389b7835e237e06 TO tag_name_unique');
        $this->addSql('ALTER TABLE tag RENAME INDEX uniq_389b783989d9b62 TO tag_slug_unique');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495126AC48 ON user (mail)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D6495126AC48 ON user');
        $this->addSql('ALTER TABLE tag RENAME INDEX tag_name_unique TO UNIQ_389B7835E237E06');
        $this->addSql('ALTER TABLE tag RENAME INDEX tag_slug_unique TO UNIQ_389B783989D9B62');
    }
}
