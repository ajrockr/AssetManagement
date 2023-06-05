<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230605021037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset_distribution ADD COLUMN location VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_distribution AS SELECT id, device_id, user_id, created_at, distributed_at, distribution_set_by, distributed_by, notes FROM asset_distribution');
        $this->addSql('DROP TABLE asset_distribution');
        $this->addSql('CREATE TABLE asset_distribution (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, device_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , distributed_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , distribution_set_by INTEGER NOT NULL, distributed_by INTEGER DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO asset_distribution (id, device_id, user_id, created_at, distributed_at, distribution_set_by, distributed_by, notes) SELECT id, device_id, user_id, created_at, distributed_at, distribution_set_by, distributed_by, notes FROM __temp__asset_distribution');
        $this->addSql('DROP TABLE __temp__asset_distribution');
    }
}
