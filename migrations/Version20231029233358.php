<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231029233358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alert_message (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) DEFAULT NULL, message VARCHAR(255) NOT NULL, date_created DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', active TINYINT(1) NOT NULL, source VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, serialnumber VARCHAR(255) DEFAULT NULL, assettag VARCHAR(255) DEFAULT NULL, purchasedate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', purchasedfrom VARCHAR(255) DEFAULT NULL, warrantystartdate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', warrantyenddate DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', `condition` VARCHAR(255) DEFAULT NULL, make VARCHAR(255) DEFAULT NULL, model VARCHAR(255) DEFAULT NULL, decomisioned TINYINT(1) DEFAULT NULL, assigned_to INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_collection (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, collected_from INT NOT NULL, collected_by INT NOT NULL, collected_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', collection_notes VARCHAR(255) DEFAULT NULL, collection_location VARCHAR(255) DEFAULT NULL, checkedout TINYINT(1) NOT NULL, processed TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_distribution (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', distributed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', distribution_set_by INT NOT NULL, distributed_by INT DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_storage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, location INT DEFAULT NULL, storage_data JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_user_field (id INT AUTO_INCREMENT NOT NULL, field_name VARCHAR(255) NOT NULL, field_value VARCHAR(255) NOT NULL, fillable TINYINT(1) NOT NULL, display TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, parent_location INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repair (id INT AUTO_INCREMENT NOT NULL, tech_id_id INT DEFAULT NULL, asset_id INT NOT NULL, created_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', started_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', resolved_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', technician_id INT DEFAULT NULL, issue VARCHAR(255) DEFAULT NULL, parts_needed LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', actions_performed LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', status VARCHAR(255) NOT NULL, last_modified_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', users_following LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', asset_unique_identifier VARCHAR(255) NOT NULL, INDEX IDX_8EE43421A85FFFCD (tech_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repair_parts (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, cost INT NOT NULL, vendor VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_config (id INT AUTO_INCREMENT NOT NULL, config_name VARCHAR(255) NOT NULL, config_value VARCHAR(255) DEFAULT NULL, config_description VARCHAR(255) DEFAULT NULL, default_value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site_view (id INT AUTO_INCREMENT NOT NULL, count INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE storage_lock (id INT AUTO_INCREMENT NOT NULL, storage_id INT NOT NULL, locked TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, extension VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, homepage VARCHAR(255) DEFAULT NULL, manager VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, microsoft_id VARCHAR(255) DEFAULT NULL, date_created DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', surname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) DEFAULT 1, pending TINYINT(1) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, user_unique_id VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, last_activity DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_roles (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(255) NOT NULL, role_value VARCHAR(255) NOT NULL, role_description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, phone1 VARCHAR(255) DEFAULT NULL, phone2 VARCHAR(255) DEFAULT NULL, primary_contact_name VARCHAR(255) DEFAULT NULL, primary_contact_phone VARCHAR(255) DEFAULT NULL, primary_contact_email VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE repair ADD CONSTRAINT FK_8EE43421A85FFFCD FOREIGN KEY (tech_id_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repair DROP FOREIGN KEY FK_8EE43421A85FFFCD');
        $this->addSql('DROP TABLE alert_message');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE asset_collection');
        $this->addSql('DROP TABLE asset_distribution');
        $this->addSql('DROP TABLE asset_storage');
        $this->addSql('DROP TABLE custom_user_field');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE repair');
        $this->addSql('DROP TABLE repair_parts');
        $this->addSql('DROP TABLE site_config');
        $this->addSql('DROP TABLE site_view');
        $this->addSql('DROP TABLE storage_lock');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE vendor');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
