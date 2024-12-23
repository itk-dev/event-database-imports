<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422115331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag ADD slug VARCHAR(255) NOT NULL AFTER name ');
        $this->addSql('ALTER TABLE vocabulary ADD slug VARCHAR(255) NOT NULL AFTER name ');
    }

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema); // TODO: Change the autogenerated stub

        // Update tags table
        $resultSet = $this->connection->executeQuery('SELECT * FROM tag');
        $tags = $resultSet->fetchAllAssociative();
        foreach ($tags as $tag) {
            $slug = (new AsciiSlugger())->slug($tag['name'])->lower()->toString();
            $this->connection->executeStatement('UPDATE tag SET slug = ? WHERE id = ?', [$slug, $tag['id']]);
        }

        // Update vocabulary table
        $resultSet = $this->connection->executeQuery('SELECT * FROM vocabulary');
        $vocabularies = $resultSet->fetchAllAssociative();
        foreach ($vocabularies as $vocabulary) {
            $slug = (new AsciiSlugger())->slug($vocabulary['name'])->lower()->toString();
            $this->connection->executeStatement('UPDATE vocabulary SET slug = ? WHERE id = ?', [$slug, $vocabulary['id']]);
        }

        // Add constraints
        $this->connection->executeStatement('CREATE UNIQUE INDEX UNIQ_389B783989D9B62 ON tag (slug)');
        $this->connection->executeStatement('CREATE UNIQUE INDEX UNIQ_9099C97B989D9B62 ON vocabulary (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_9099C97B989D9B62 ON vocabulary');
        $this->addSql('ALTER TABLE vocabulary DROP slug');
        $this->addSql('DROP INDEX UNIQ_389B783989D9B62 ON tag');
        $this->addSql('ALTER TABLE tag DROP slug');
    }
}
