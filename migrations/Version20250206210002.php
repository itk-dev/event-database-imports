<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206210002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name_mail_unique ON organization');
        $this->addSql('ALTER TABLE organization RENAME INDEX uniq_c1ee637c5e237e06 TO name_unique');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX name_mail_unique ON organization (name, mail)');
        $this->addSql('ALTER TABLE organization RENAME INDEX name_unique TO UNIQ_C1EE637C5E237E06');
    }
}
