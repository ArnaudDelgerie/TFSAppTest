<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at to app_note';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE app_note ADD COLUMN created_at DATETIME NOT NULL DEFAULT '2026-01-01 00:00:00'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_note DROP COLUMN created_at');
    }
}
