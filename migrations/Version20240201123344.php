<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201123344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD updated_by_id INT NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE location suchnummer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_81398E09896DBBDE ON customer (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09896DBBDE');
        $this->addSql('DROP INDEX IDX_81398E09896DBBDE ON customer');
        $this->addSql('ALTER TABLE customer DROP updated_by_id, DROP updated_at, CHANGE suchnummer location VARCHAR(255) NOT NULL');
    }
}
