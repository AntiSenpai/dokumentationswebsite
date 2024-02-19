<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202084424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_documentation (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, updated_by_id INT NOT NULL, content LONGTEXT NOT NULL, section_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_17B0D93864D218E (location_id), INDEX IDX_17B0D938896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location_documentation (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, INDEX IDX_D9AE423464D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_documentation ADD CONSTRAINT FK_17B0D93864D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE customer_documentation ADD CONSTRAINT FK_17B0D938896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE location_documentation ADD CONSTRAINT FK_D9AE423464D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE location ADD customer_id_id INT NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBB171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_5E9E89CBB171EB6C ON location (customer_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_documentation DROP FOREIGN KEY FK_17B0D93864D218E');
        $this->addSql('ALTER TABLE customer_documentation DROP FOREIGN KEY FK_17B0D938896DBBDE');
        $this->addSql('ALTER TABLE location_documentation DROP FOREIGN KEY FK_D9AE423464D218E');
        $this->addSql('DROP TABLE customer_documentation');
        $this->addSql('DROP TABLE location_documentation');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBB171EB6C');
        $this->addSql('DROP INDEX IDX_5E9E89CBB171EB6C ON location');
        $this->addSql('ALTER TABLE location DROP customer_id_id, DROP address, DROP description');
    }
}
