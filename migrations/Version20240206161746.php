<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240206161746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alter Asset table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS asset');
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, serial_number VARCHAR(255) DEFAULT NULL, asset_tag VARCHAR(255) DEFAULT NULL, purchase_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', purchased_from VARCHAR(255) DEFAULT NULL, warranty_start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', warranty_end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', asset_condition VARCHAR(255) DEFAULT NULL, make VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, decommissioned TINYINT(1) DEFAULT NULL, assigned_to INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS asset');
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, serialnumber VARCHAR(255) DEFAULT NULL, assettag VARCHAR(255) DEFAULT NULL, purchasedate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', purchasedfrom VARCHAR(255) DEFAULT NULL, warrantystartdate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', warrantyenddate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', `condition` VARCHAR(255) DEFAULT NULL, make VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, decomisioned TINYINT(1) DEFAULT NULL, assigned_to INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }
}
