<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230528150550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asset_collection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, collected_from INTEGER NOT NULL, collected_by INTEGER NOT NULL, collected_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , collection_notes VARCHAR(255) DEFAULT NULL, collection_location VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE asset_storage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, location INTEGER DEFAULT NULL, storage_data CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('CREATE TABLE location (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, parent_location INTEGER DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE asset_collection');
        $this->addSql('DROP TABLE asset_storage');
        $this->addSql('DROP TABLE location');
    }
}
