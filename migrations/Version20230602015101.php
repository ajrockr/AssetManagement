<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602015101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE repair (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, created_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , started_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , resolved_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , technician_id INTEGER DEFAULT NULL, issue VARCHAR(255) DEFAULT NULL, parts_needed CLOB DEFAULT NULL --(DC2Type:array)
        , actions_performed CLOB DEFAULT NULL --(DC2Type:array)
        , status VARCHAR(255) NOT NULL, last_modified_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , users_following CLOB DEFAULT NULL --(DC2Type:array)
        )');
        $this->addSql('DROP TABLE reset_password_request');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_password_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, selector VARCHAR(20) NOT NULL COLLATE "BINARY", hashed_token VARCHAR(100) NOT NULL COLLATE "BINARY", requested_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('DROP TABLE repair');
    }
}
