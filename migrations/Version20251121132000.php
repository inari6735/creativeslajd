<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User table and user_id to slideshow table';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        
        // Add user_id to slideshow table
        $this->addSql('ALTER TABLE slideshow ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE slideshow ADD CONSTRAINT FK_70B3613A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_70B3613A76ED395 ON slideshow (user_id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key and user_id from slideshow
        $this->addSql('ALTER TABLE slideshow DROP CONSTRAINT FK_70B3613A76ED395');
        $this->addSql('DROP INDEX IDX_70B3613A76ED395');
        $this->addSql('ALTER TABLE slideshow DROP user_id');
        
        // Drop user table
        $this->addSql('DROP TABLE "user"');
    }
}
