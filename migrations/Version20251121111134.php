<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121111134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE slideshow ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('UPDATE slideshow SET updated_at = created_at WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE slideshow ALTER COLUMN updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE slideshow DROP slide_duration');
        $this->addSql('ALTER TABLE slideshow DROP transition_effect');
        $this->addSql('ALTER TABLE slideshow DROP autoplay');
        $this->addSql('ALTER TABLE slideshow DROP loop');
        $this->addSql('ALTER TABLE slideshow DROP random_order');
        $this->addSql('ALTER TABLE slideshow DROP view_count');
        $this->addSql('ALTER TABLE slideshow DROP last_viewed_at');
        $this->addSql('ALTER TABLE slideshow DROP is_public');
        $this->addSql('ALTER TABLE slideshow ALTER description TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE slideshow ADD slide_duration INT DEFAULT 5 NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD transition_effect VARCHAR(50) DEFAULT \'fade\' NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD autoplay BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD loop BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD random_order BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD view_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD last_viewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE slideshow ADD is_public BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE slideshow DROP updated_at');
        $this->addSql('ALTER TABLE slideshow ALTER description TYPE VARCHAR(500)');
    }
}
