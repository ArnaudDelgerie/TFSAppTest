<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715220100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deliberately invalid migration 2/2 (plan 020 snapshot/restore validation)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_widget ADD COLUMN note VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_widget DROP COLUMN note');
    }
}
