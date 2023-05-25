<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525124030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset ADD COLUMN assigned_to INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset AS SELECT id, serialnumber, assettag, purchasedate, purchasedfrom, warrantystartdate, warrantyenddate, condition, make, model, decomisioned FROM asset');
        $this->addSql('DROP TABLE asset');
        $this->addSql('CREATE TABLE asset (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, serialnumber VARCHAR(255) DEFAULT NULL, assettag VARCHAR(255) DEFAULT NULL, purchasedate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , purchasedfrom VARCHAR(255) DEFAULT NULL, warrantystartdate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , warrantyenddate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , condition VARCHAR(255) DEFAULT NULL, make VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, decomisioned BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO asset (id, serialnumber, assettag, purchasedate, purchasedfrom, warrantystartdate, warrantyenddate, condition, make, model, decomisioned) SELECT id, serialnumber, assettag, purchasedate, purchasedfrom, warrantystartdate, warrantyenddate, condition, make, model, decomisioned FROM __temp__asset');
        $this->addSql('DROP TABLE __temp__asset');
    }
}
