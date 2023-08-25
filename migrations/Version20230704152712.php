<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230704152712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE playlist_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE playlist (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, thumbnail VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE playlist_video (playlist_id INT NOT NULL, video_id INT NOT NULL, PRIMARY KEY(playlist_id, video_id))');
        $this->addSql('CREATE INDEX IDX_DFDBC36F6BBD148 ON playlist_video (playlist_id)');
        $this->addSql('CREATE INDEX IDX_DFDBC36F29C1004E ON playlist_video (video_id)');
        $this->addSql('ALTER TABLE playlist_video ADD CONSTRAINT FK_DFDBC36F6BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE playlist_video ADD CONSTRAINT FK_DFDBC36F29C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE playlist_id_seq CASCADE');
        $this->addSql('ALTER TABLE playlist_video DROP CONSTRAINT FK_DFDBC36F6BBD148');
        $this->addSql('ALTER TABLE playlist_video DROP CONSTRAINT FK_DFDBC36F29C1004E');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE playlist_video');
    }
}
