<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261123231821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter ADD created_at DATETIME NOT NULL, ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52EF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F981B52EF675F31B ON chapter (author_id)');
        $this->addSql('ALTER TABLE chapter_section ADD updated_at DATETIME NOT NULL');
        // Existing rows have no history: date them to the migration.
        $this->addSql('UPDATE chapter SET created_at = NOW()');
        $this->addSql('UPDATE chapter_section SET updated_at = NOW()');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52EF675F31B');
        $this->addSql('DROP INDEX IDX_F981B52EF675F31B ON chapter');
        $this->addSql('ALTER TABLE chapter DROP created_at, DROP author_id');
        $this->addSql('ALTER TABLE chapter_section DROP updated_at');
    }
}
