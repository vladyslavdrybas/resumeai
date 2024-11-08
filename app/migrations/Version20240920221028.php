<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920221028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rsai_user_google ADD owner_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN rsai_user_google.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE rsai_user_google ADD CONSTRAINT FK_1ECE49EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES rsai_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1ECE49EE7E3C61F9 ON rsai_user_google (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE rsai_user_google DROP CONSTRAINT FK_1ECE49EE7E3C61F9');
        $this->addSql('DROP INDEX IDX_1ECE49EE7E3C61F9');
        $this->addSql('ALTER TABLE rsai_user_google DROP owner_id');
    }
}
