<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251111233619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE device_login_request (id SERIAL NOT NULL, user_id INT DEFAULT NULL, device_code_hash VARCHAR(128) NOT NULL, user_code VARCHAR(16) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, consumed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, poll_interval INT DEFAULT 5 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA1C0647DCB31CE8 ON device_login_request (device_code_hash)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA1C0647D947C51 ON device_login_request (user_code)');
        $this->addSql('CREATE INDEX IDX_DA1C0647A76ED395 ON device_login_request (user_id)');
        $this->addSql('CREATE INDEX device_code_hash_idx ON device_login_request (device_code_hash)');
        $this->addSql('COMMENT ON COLUMN device_login_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN device_login_request.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN device_login_request.approved_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN device_login_request.consumed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE device_login_request ADD CONSTRAINT FK_DA1C0647A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE device_login_request DROP CONSTRAINT FK_DA1C0647A76ED395');
        $this->addSql('DROP TABLE device_login_request');
    }
}
