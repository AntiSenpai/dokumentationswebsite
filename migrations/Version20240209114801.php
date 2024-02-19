<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240209114801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zeiterfassung ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE zeiterfassung ADD CONSTRAINT FK_8530B045A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8530B045A76ED395 ON zeiterfassung (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zeiterfassung DROP FOREIGN KEY FK_8530B045A76ED395');
        $this->addSql('DROP INDEX IDX_8530B045A76ED395 ON zeiterfassung');
        $this->addSql('ALTER TABLE zeiterfassung DROP user_id');
    }
}
