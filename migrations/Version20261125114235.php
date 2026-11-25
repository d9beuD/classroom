<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125114235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fold table sections into paragraph (Content) sections: "table" is no longer a section type.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE chapter_section SET type = 'paragraph' WHERE type = 'table'");
    }

    public function down(Schema $schema): void
    {
        // Irreversible: which paragraphs were tables isn't recorded, and they render the same.
    }
}
