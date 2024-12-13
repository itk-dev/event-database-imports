<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313090154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP suite');
        $this->addSql('CREATE UNIQUE INDEX street_postcode_unique ON address (street, postal_code)');
        $this->addSql('CREATE UNIQUE INDEX location_unique ON location (name, url, mail)');
        $this->addSql('CREATE UNIQUE INDEX name_mail_unique ON organization (name, mail)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name_mail_unique ON organization');
        $this->addSql('DROP INDEX street_postcode_unique ON address');
        $this->addSql('ALTER TABLE address ADD suite VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX location_unique ON location');
    }
}
