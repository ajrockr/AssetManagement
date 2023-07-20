<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117153307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_user_field ADD COLUMN display BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__custom_user_field AS SELECT id, field_name, field_value, fillable FROM custom_user_field');
        $this->addSql('DROP TABLE custom_user_field');
        $this->addSql('CREATE TABLE custom_user_field (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, field_name VARCHAR(255) NOT NULL, field_value VARCHAR(255) NOT NULL, fillable BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO custom_user_field (id, field_name, field_value, fillable) SELECT id, field_name, field_value, fillable FROM __temp__custom_user_field');
        $this->addSql('DROP TABLE __temp__custom_user_field');
    }
}
