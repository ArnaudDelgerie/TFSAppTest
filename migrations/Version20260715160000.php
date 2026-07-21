<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create app_note table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_note (id INTEGER NOT NULL, body VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_note');
    }
}
