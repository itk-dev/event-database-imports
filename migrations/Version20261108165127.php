<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Store admin uploaded images as "uploads/<file>", relative to the image root like fetched images ("<folder>/<file>").
 *
 * The image import stored admin uploads as "/uploads/<file>". EasyAdmin's upload field rejects a leading slash, so
 * editing an event cleared its image. A few uploads were never processed by the import and are stored as "<file>".
 */
final class Version20261108165127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store admin uploaded images as "uploads/<file>"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE image SET local = SUBSTRING(local, 2) WHERE local LIKE '/uploads/_%'");
        $this->addSql("UPDATE image SET local = CONCAT('uploads/', local) WHERE local <> '' AND local NOT LIKE '%/%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE image SET local = CONCAT('/', local) WHERE local LIKE 'uploads/_%'");
    }
}
