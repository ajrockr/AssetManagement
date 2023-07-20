<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602020715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repair ADD COLUMN asset_unique_identifier VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__repair AS SELECT id, asset_id, created_date, started_date, resolved_date, technician_id, issue, parts_needed, actions_performed, status, last_modified_date, users_following FROM repair');
        $this->addSql('DROP TABLE repair');
        $this->addSql('CREATE TABLE repair (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, created_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , started_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , resolved_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , technician_id INTEGER DEFAULT NULL, issue VARCHAR(255) DEFAULT NULL, parts_needed CLOB DEFAULT NULL --(DC2Type:array)
        , actions_performed CLOB DEFAULT NULL --(DC2Type:array)
        , status VARCHAR(255) NOT NULL, last_modified_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , users_following CLOB DEFAULT NULL --(DC2Type:array)
        )');
        $this->addSql('INSERT INTO repair (id, asset_id, created_date, started_date, resolved_date, technician_id, issue, parts_needed, actions_performed, status, last_modified_date, users_following) SELECT id, asset_id, created_date, started_date, resolved_date, technician_id, issue, parts_needed, actions_performed, status, last_modified_date, users_following FROM __temp__repair');
        $this->addSql('DROP TABLE __temp__repair');
    }
}
