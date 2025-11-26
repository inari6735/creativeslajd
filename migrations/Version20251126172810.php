<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126172810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE slide ADD youtube_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE slide ALTER image_path DROP NOT NULL');
        $this->addSql('ALTER TABLE slide ALTER media_type DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE slide DROP youtube_url');
        $this->addSql('ALTER TABLE slide ALTER image_path SET NOT NULL');
        $this->addSql('ALTER TABLE slide ALTER media_type SET DEFAULT \'image\'');
    }
}
