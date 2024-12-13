<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231127121531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD editable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE event ADD editable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE image ADD editable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE location ADD editable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE occurrence ADD editable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE tag ADD editable TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE occurrence DROP editable');
        $this->addSql('ALTER TABLE location DROP editable');
        $this->addSql('ALTER TABLE event DROP editable');
        $this->addSql('ALTER TABLE tag DROP editable');
        $this->addSql('ALTER TABLE address DROP editable');
        $this->addSql('ALTER TABLE image DROP editable');
    }
}
