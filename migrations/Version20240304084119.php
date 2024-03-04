<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240304084119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD technischer_ansprechpartner_id INT DEFAULT NULL, ADD adresse VARCHAR(255) NOT NULL, ADD vor_ort_ansprechpartner VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E095EA6019B FOREIGN KEY (technischer_ansprechpartner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_81398E095EA6019B ON customer (technischer_ansprechpartner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E095EA6019B');
        $this->addSql('DROP INDEX IDX_81398E095EA6019B ON customer');
        $this->addSql('ALTER TABLE customer DROP technischer_ansprechpartner_id, DROP adresse, DROP vor_ort_ansprechpartner, DROP email');
    }
}
