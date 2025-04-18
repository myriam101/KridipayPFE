<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411090419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonif_point CHANGE date_use date_use DATETIME NOT NULL');
        $this->addSql('ALTER TABLE simulation ADD id_client INT NOT NULL');
        $this->addSql('ALTER TABLE simulation ADD CONSTRAINT FK_CBDA467BE173B1B8 FOREIGN KEY (id_client) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CBDA467BE173B1B8 ON simulation (id_client)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonif_point CHANGE date_use date_use DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE simulation DROP FOREIGN KEY FK_CBDA467BE173B1B8');
        $this->addSql('DROP INDEX IDX_CBDA467BE173B1B8 ON simulation');
        $this->addSql('ALTER TABLE simulation DROP id_client');
    }
}
