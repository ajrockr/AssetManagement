<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027213138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_collection AS SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed FROM asset_collection');
        $this->addSql('DROP TABLE asset_collection');
        $this->addSql('CREATE TABLE asset_collection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, collected_from INTEGER NOT NULL, collected_by INTEGER NOT NULL, collected_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , collection_notes VARCHAR(255) DEFAULT NULL, collection_location VARCHAR(255) DEFAULT NULL, checkedout BOOLEAN NOT NULL, processed BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO asset_collection (id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed) SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed FROM __temp__asset_collection');
        $this->addSql('DROP TABLE __temp__asset_collection');
        $this->addSql('CREATE TEMPORARY TABLE __temp__custom_user_field AS SELECT id, field_name, field_value, fillable, display FROM custom_user_field');
        $this->addSql('DROP TABLE custom_user_field');
        $this->addSql('CREATE TABLE custom_user_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, field_name VARCHAR(255) NOT NULL, field_value VARCHAR(255) NOT NULL, fillable BOOLEAN NOT NULL, display BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO custom_user_field (id, field_name, field_value, fillable, display) SELECT id, field_name, field_value, fillable, display FROM __temp__custom_user_field');
        $this->addSql('DROP TABLE __temp__custom_user_field');
        $this->addSql('ALTER TABLE user ADD COLUMN last_activity DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_collection AS SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed FROM asset_collection');
        $this->addSql('DROP TABLE asset_collection');
        $this->addSql('CREATE TABLE asset_collection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, collected_from INTEGER NOT NULL, collected_by INTEGER NOT NULL, collected_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , collection_notes VARCHAR(255) DEFAULT NULL, collection_location VARCHAR(255) DEFAULT NULL, checkedout BOOLEAN DEFAULT NULL, processed BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO asset_collection (id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed) SELECT id, device_id, collected_from, collected_by, collected_date, collection_notes, collection_location, checkedout, processed FROM __temp__asset_collection');
        $this->addSql('DROP TABLE __temp__asset_collection');
        $this->addSql('CREATE TEMPORARY TABLE __temp__custom_user_field AS SELECT id, field_name, field_value, fillable, display FROM custom_user_field');
        $this->addSql('DROP TABLE custom_user_field');
        $this->addSql('CREATE TABLE custom_user_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, field_name VARCHAR(255) NOT NULL, field_value VARCHAR(255) NOT NULL, fillable BOOLEAN NOT NULL, display BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO custom_user_field (id, field_name, field_value, fillable, display) SELECT id, field_name, field_value, fillable, display FROM __temp__custom_user_field');
        $this->addSql('DROP TABLE __temp__custom_user_field');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname, enabled, pending, avatar, user_unique_id, type FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, extension VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, homepage VARCHAR(255) DEFAULT NULL, manager VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, microsoft_id VARCHAR(255) DEFAULT NULL, date_created DATE DEFAULT NULL --(DC2Type:date_immutable)
        , surname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, enabled BOOLEAN DEFAULT 1, pending BOOLEAN DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, user_unique_id VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname, enabled, pending, avatar, user_unique_id, type) SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname, enabled, pending, avatar, user_unique_id, type FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}
