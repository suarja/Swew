<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112163204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignment (id SERIAL NOT NULL, lesson_id INT NOT NULL, code VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, cli_steps TEXT DEFAULT NULL, evaluation_notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, display_order SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_30C544BA77153098 ON assignment (code)');
        $this->addSql('CREATE INDEX IDX_30C544BACDF80196 ON assignment (lesson_id)');
        $this->addSql('COMMENT ON COLUMN assignment.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN assignment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE course (id SERIAL NOT NULL, slug VARCHAR(180) NOT NULL, title VARCHAR(255) NOT NULL, summary TEXT DEFAULT NULL, status VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_169E6FB9989D9B62 ON course (slug)');
        $this->addSql('COMMENT ON COLUMN course.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN course.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE lesson (id SERIAL NOT NULL, course_id INT NOT NULL, slug VARCHAR(180) NOT NULL, title VARCHAR(255) NOT NULL, summary TEXT DEFAULT NULL, content TEXT DEFAULT NULL, sequence_position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F87474F3591CC992 ON lesson (course_id)');
        $this->addSql('COMMENT ON COLUMN lesson.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lesson.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BACDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BACDF80196');
        $this->addSql('ALTER TABLE lesson DROP CONSTRAINT FK_F87474F3591CC992');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lesson');
    }
}
