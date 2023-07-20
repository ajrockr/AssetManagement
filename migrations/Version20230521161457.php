<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230521161457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, extension VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, homepage VARCHAR(255) DEFAULT NULL, manager VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, microsoft_id VARCHAR(255) DEFAULT NULL, date_created DATE DEFAULT NULL --(DC2Type:date_immutable)
        , surname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, enabled BOOLEAN DEFAULT 1)');
        $this->addSql('INSERT INTO user (id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname) SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, department VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, extension VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, homepage VARCHAR(255) DEFAULT NULL, manager VARCHAR(255) DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, microsoft_id VARCHAR(255) DEFAULT NULL, date_created DATE DEFAULT NULL --(DC2Type:date_immutable)
        , surname VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname) SELECT id, username, roles, password, email, location, department, phone, extension, title, homepage, manager, google_id, microsoft_id, date_created, surname, firstname FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
    }
}
