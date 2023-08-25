<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804140038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE self_defense_id_seq CASCADE');
        $this->addSql('DROP TABLE self_defense');
        $this->addSql('ALTER TABLE video ADD instructor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video ADD category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE video ADD free BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE video ALTER base_position DROP NOT NULL');
        $this->addSql('ALTER TABLE video ALTER ending_position DROP NOT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C8C4FC193 FOREIGN KEY (instructor_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7CC7DA2C8C4FC193 ON video (instructor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE self_defense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE self_defense (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, thumbnail VARCHAR(255) NOT NULL, video VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE video DROP CONSTRAINT FK_7CC7DA2C8C4FC193');
        $this->addSql('DROP INDEX IDX_7CC7DA2C8C4FC193');
        $this->addSql('ALTER TABLE video DROP instructor_id');
        $this->addSql('ALTER TABLE video DROP category');
        $this->addSql('ALTER TABLE video DROP free');
        $this->addSql('ALTER TABLE video ALTER base_position SET NOT NULL');
        $this->addSql('ALTER TABLE video ALTER ending_position SET NOT NULL');
    }
}
