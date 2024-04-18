<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305153932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_documentation DROP FOREIGN KEY FK_17B0D93864D218E');
        $this->addSql('DROP INDEX IDX_17B0D93864D218E ON customer_documentation');
        $this->addSql('ALTER TABLE customer_documentation CHANGE location_id customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer_documentation ADD CONSTRAINT FK_17B0D9389395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_17B0D9389395C3F3 ON customer_documentation (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_documentation DROP FOREIGN KEY FK_17B0D9389395C3F3');
        $this->addSql('DROP INDEX IDX_17B0D9389395C3F3 ON customer_documentation');
        $this->addSql('ALTER TABLE customer_documentation CHANGE customer_id location_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer_documentation ADD CONSTRAINT FK_17B0D93864D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_17B0D93864D218E ON customer_documentation (location_id)');
    }
}
