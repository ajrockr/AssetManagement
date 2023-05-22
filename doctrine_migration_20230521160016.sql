-- Doctrine Migration File Generated on 2023-05-21 16:00:16

-- Version DoctrineMigrations\Version20230521155757
ALTER TABLE user ADD COLUMN enabled BOOLEAN NOT NULL;
-- Version DoctrineMigrations\Version20230521155757 update table metadata;
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20230521155757', '2023-05-21 16:00:16', 0);
