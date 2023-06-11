<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230610180352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_video (user_id INT NOT NULL, video_id INT NOT NULL, PRIMARY KEY(user_id, video_id))');
        $this->addSql('CREATE INDEX IDX_9E052174A76ED395 ON user_video (user_id)');
        $this->addSql('CREATE INDEX IDX_9E05217429C1004E ON user_video (video_id)');
        $this->addSql('ALTER TABLE user_video ADD CONSTRAINT FK_9E052174A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_video ADD CONSTRAINT FK_9E05217429C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_video DROP CONSTRAINT FK_9E052174A76ED395');
        $this->addSql('ALTER TABLE user_video DROP CONSTRAINT FK_9E05217429C1004E');
        $this->addSql('DROP TABLE user_video');
    }
}
