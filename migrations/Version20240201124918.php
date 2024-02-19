<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201124918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, unterstandort_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, ist_hauptstandort TINYINT(1) NOT NULL, INDEX IDX_5E9E89CB9395C3F3 (customer_id), INDEX IDX_5E9E89CBA0A849F5 (unterstandort_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA0A849F5 FOREIGN KEY (unterstandort_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB9395C3F3');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBA0A849F5');
        $this->addSql('DROP TABLE location');
    }
}
