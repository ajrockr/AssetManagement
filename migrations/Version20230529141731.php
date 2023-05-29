<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230529141731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_collection ADD COLUMN checkedout BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_collection AS SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location FROM asset_collection');
        $this->addSql('DROP TABLE asset_collection');
        $this->addSql('CREATE TABLE asset_collection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, collected_from INTEGER NOT NULL, collected_by INTEGER NOT NULL, collected_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , collection_notes VARCHAR(255) DEFAULT NULL, collection_location VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO asset_collection (id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location) SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location FROM __temp__asset_collection');
        $this->addSql('DROP TABLE __temp__asset_collection');
    }
}
