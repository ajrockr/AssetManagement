<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616215207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE alert_message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject VARCHAR(255) DEFAULT NULL COLLATE "BINARY", message VARCHAR(255) NOT NULL COLLATE "BINARY", date_created DATE DEFAULT NULL --(DC2Type:date_immutable)
        , active BOOLEAN NOT NULL, source VARCHAR(255) NOT NULL COLLATE "BINARY")');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE asset (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, serialnumber VARCHAR(255) DEFAULT NULL COLLATE "BINARY", assettag VARCHAR(255) DEFAULT NULL COLLATE "BINARY", purchasedate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , purchasedfrom VARCHAR(255) DEFAULT NULL COLLATE "BINARY", warrantystartdate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , warrantyenddate DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , condition VARCHAR(255) DEFAULT NULL COLLATE "BINARY", make VARCHAR(255) DEFAULT NULL COLLATE "BINARY", model VARCHAR(255) DEFAULT NULL COLLATE "BINARY", decomisioned BOOLEAN DEFAULT NULL, assigned_to INTEGER DEFAULT NULL)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE asset_collection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, collected_from INTEGER NOT NULL, collected_by INTEGER NOT NULL, collected_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , collection_notes VARCHAR(255) DEFAULT NULL COLLATE "BINARY", collection_location VARCHAR(255) DEFAULT NULL COLLATE "BINARY", checkedout BOOLEAN DEFAULT NULL, processed BOOLEAN DEFAULT NULL)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE asset_distribution (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , distributed_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , distribution_set_by INTEGER NOT NULL, distributed_by INTEGER DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL COLLATE "BINARY", location VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE asset_storage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", description VARCHAR(255) DEFAULT NULL COLLATE "BINARY", location INTEGER DEFAULT NULL, storage_data CLOB NOT NULL COLLATE "BINARY" --(DC2Type:json)
        )');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE custom_user_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, field_name VARCHAR(255) NOT NULL COLLATE "BINARY", field_value VARCHAR(255) NOT NULL COLLATE "BINARY", fillable BOOLEAN NOT NULL, display BOOLEAN DEFAULT NULL)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE location (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", description VARCHAR(255) DEFAULT NULL COLLATE "BINARY", parent_location INTEGER DEFAULT NULL)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL COLLATE "BINARY", headers CLOB NOT NULL COLLATE "BINARY", queue_name VARCHAR(190) NOT NULL COLLATE "BINARY", created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE repair (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tech_id_id INTEGER DEFAULT NULL, asset_id INTEGER NOT NULL, created_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , started_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , resolved_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , technician_id INTEGER DEFAULT NULL, issue VARCHAR(255) DEFAULT NULL COLLATE "BINARY", parts_needed CLOB DEFAULT NULL COLLATE "BINARY" --(DC2Type:array)
        , actions_performed CLOB DEFAULT NULL COLLATE "BINARY" --(DC2Type:array)
        , status VARCHAR(255) NOT NULL COLLATE "BINARY", last_modified_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , users_following CLOB DEFAULT NULL COLLATE "BINARY" --(DC2Type:array)
        , asset_unique_identifier VARCHAR(255) NOT NULL COLLATE "BINARY", CONSTRAINT FK_8EE43421A85FFFCD FOREIGN KEY (tech_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8EE43421A85FFFCD ON repair (tech_id_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE repair_parts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", description VARCHAR(255) DEFAULT NULL COLLATE "BINARY", cost INTEGER NOT NULL, vendor VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE site_config (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_name VARCHAR(255) NOT NULL COLLATE "BINARY", config_value VARCHAR(255) DEFAULT NULL COLLATE "BINARY", config_description VARCHAR(255) DEFAULT NULL COLLATE "BINARY", default_value VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE site_view (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, count INTEGER NOT NULL)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL COLLATE "BINARY", roles CLOB NOT NULL COLLATE "BINARY" --(DC2Type:json)
        , password VARCHAR(255) DEFAULT NULL COLLATE "BINARY", email VARCHAR(255) DEFAULT NULL COLLATE "BINARY", location VARCHAR(255) DEFAULT NULL COLLATE "BINARY", department VARCHAR(255) DEFAULT NULL COLLATE "BINARY", phone VARCHAR(255) DEFAULT NULL COLLATE "BINARY", extension VARCHAR(255) DEFAULT NULL COLLATE "BINARY", title VARCHAR(255) DEFAULT NULL COLLATE "BINARY", homepage VARCHAR(255) DEFAULT NULL COLLATE "BINARY", manager VARCHAR(255) DEFAULT NULL COLLATE "BINARY", google_id VARCHAR(255) DEFAULT NULL COLLATE "BINARY", microsoft_id VARCHAR(255) DEFAULT NULL COLLATE "BINARY", date_created DATE DEFAULT NULL --(DC2Type:date_immutable)
        , surname VARCHAR(255) DEFAULT NULL COLLATE "BINARY", firstname VARCHAR(255) DEFAULT NULL COLLATE "BINARY", enabled BOOLEAN DEFAULT 1, pending BOOLEAN DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL COLLATE "BINARY", user_unique_id VARCHAR(255) DEFAULT NULL COLLATE "BINARY", type VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE user_roles (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role_name VARCHAR(255) NOT NULL COLLATE "BINARY", role_value VARCHAR(255) NOT NULL COLLATE "BINARY", role_description VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('CREATE TABLE vendor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", phone1 VARCHAR(255) DEFAULT NULL COLLATE "BINARY", phone2 VARCHAR(255) DEFAULT NULL COLLATE "BINARY", primary_contact_name VARCHAR(255) DEFAULT NULL COLLATE "BINARY", primary_contact_phone VARCHAR(255) DEFAULT NULL COLLATE "BINARY", primary_contact_email VARCHAR(255) DEFAULT NULL COLLATE "BINARY", address VARCHAR(255) DEFAULT NULL COLLATE "BINARY", website VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE alert_message');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE asset');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE asset_collection');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE asset_distribution');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE asset_storage');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE custom_user_field');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE location');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE messenger_messages');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE repair');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE repair_parts');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE site_config');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE site_view');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE user');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE user_roles');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\SqlitePlatform'."
        );

        $this->addSql('DROP TABLE vendor');
    }
}
