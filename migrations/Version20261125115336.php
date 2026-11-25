<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125115336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename the "paragraph" section type to "content", after the Content entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE chapter_section SET type = 'content' WHERE type = 'paragraph'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE chapter_section SET type = 'paragraph' WHERE type = 'content'");
    }
}
