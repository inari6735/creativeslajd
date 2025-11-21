<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121145900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isPubliclyEditable and editToken to slideshow table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slideshow ADD is_publicly_editable BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE slideshow ADD edit_token VARCHAR(32) DEFAULT NULL');
        
        // Generate unique tokens for existing slideshows
        $this->addSql("UPDATE slideshow SET edit_token = md5(id::text || 'edit' || extract(epoch from now())::text) WHERE edit_token IS NULL");
        
        $this->addSql('ALTER TABLE slideshow ALTER COLUMN edit_token SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70B3613E5C84E21 ON slideshow (edit_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_70B3613E5C84E21');
        $this->addSql('ALTER TABLE slideshow DROP is_publicly_editable');
        $this->addSql('ALTER TABLE slideshow DROP edit_token');
    }
}
