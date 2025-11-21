<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121135000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isPublic and shareToken to slideshow table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slideshow ADD is_public BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD share_token VARCHAR(32) DEFAULT NULL');
        
        // Generate unique tokens for existing slideshows using md5 of id and timestamp
        $this->addSql("UPDATE slideshow SET share_token = md5(id::text || extract(epoch from now())::text) WHERE share_token IS NULL");
        
        $this->addSql('ALTER TABLE slideshow ALTER COLUMN share_token SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70B3613FBE7C6CE ON slideshow (share_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_70B3613FBE7C6CE');
        $this->addSql('ALTER TABLE slideshow DROP is_public');
        $this->addSql('ALTER TABLE slideshow DROP share_token');
    }
}
