<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261121142132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, excerpt VARCHAR(255) NOT NULL, position INT NOT NULL, course_id INT NOT NULL, INDEX IDX_F981B52E591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chapter_feedback (id INT AUTO_INCREMENT NOT NULL, note VARCHAR(15) NOT NULL, comment VARCHAR(1023) DEFAULT NULL, chapter_id INT NOT NULL, student_id INT DEFAULT NULL, INDEX IDX_3430295D579F4768 (chapter_id), INDEX IDX_3430295DCB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chapter_section (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, parent_id INT DEFAULT NULL, chapter_id INT NOT NULL, INDEX IDX_57427EA0727ACA70 (parent_id), INDEX IDX_57427EA0579F4768 (chapter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE classroom (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, school_id INT DEFAULT NULL, INDEX IDX_497D309DC32A47EE (school_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) NOT NULL, icon VARCHAR(20) NOT NULL, icon_color VARCHAR(20) NOT NULL, icon_color_alt VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE school (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, logo VARCHAR(255) NOT NULL, city VARCHAR(15) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, classroom_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), INDEX IDX_8D93D6496278D5A8 (classroom_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE chapter_feedback ADD CONSTRAINT FK_3430295D579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE chapter_feedback ADD CONSTRAINT FK_3430295DCB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE chapter_section ADD CONSTRAINT FK_57427EA0727ACA70 FOREIGN KEY (parent_id) REFERENCES chapter_section (id)');
        $this->addSql('ALTER TABLE chapter_section ADD CONSTRAINT FK_57427EA0579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE classroom ADD CONSTRAINT FK_497D309DC32A47EE FOREIGN KEY (school_id) REFERENCES school (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6496278D5A8 FOREIGN KEY (classroom_id) REFERENCES classroom (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E591CC992');
        $this->addSql('ALTER TABLE chapter_feedback DROP FOREIGN KEY FK_3430295D579F4768');
        $this->addSql('ALTER TABLE chapter_feedback DROP FOREIGN KEY FK_3430295DCB944F1A');
        $this->addSql('ALTER TABLE chapter_section DROP FOREIGN KEY FK_57427EA0727ACA70');
        $this->addSql('ALTER TABLE chapter_section DROP FOREIGN KEY FK_57427EA0579F4768');
        $this->addSql('ALTER TABLE classroom DROP FOREIGN KEY FK_497D309DC32A47EE');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6496278D5A8');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE chapter_feedback');
        $this->addSql('DROP TABLE chapter_section');
        $this->addSql('DROP TABLE classroom');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
