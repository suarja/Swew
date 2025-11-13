<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lesson video URL and submission tracking table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson ADD video_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE submission (id SERIAL NOT NULL, user_id INT NOT NULL, assignment_id INT NOT NULL, status VARCHAR(32) NOT NULL, cli_version VARCHAR(32) NOT NULL, kit_version SMALLINT NOT NULL, checks JSON DEFAULT NULL, prompts JSON DEFAULT NULL, system_info JSON DEFAULT NULL, logs TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CF4DF1EA76ED395 ON submission (user_id)');
        $this->addSql('CREATE INDEX IDX_CF4DF1E4F5B19A4 ON submission (assignment_id)');
        $this->addSql('COMMENT ON COLUMN submission.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN submission.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_CF4DF1EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_CF4DF1E4F5B19A4 FOREIGN KEY (assignment_id) REFERENCES assignment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lesson DROP video_url');
        $this->addSql('ALTER TABLE submission DROP CONSTRAINT FK_CF4DF1EA76ED395');
        $this->addSql('ALTER TABLE submission DROP CONSTRAINT FK_CF4DF1E4F5B19A4');
        $this->addSql('DROP TABLE submission');
    }
}
