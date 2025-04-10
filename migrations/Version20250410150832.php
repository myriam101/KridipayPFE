<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410150832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonif_point ADD id_product INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bonif_point ADD CONSTRAINT FK_7CEFE78ADD7ADDD FOREIGN KEY (id_product) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_7CEFE78ADD7ADDD ON bonif_point (id_product)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonif_point DROP FOREIGN KEY FK_7CEFE78ADD7ADDD');
        $this->addSql('DROP INDEX IDX_7CEFE78ADD7ADDD ON bonif_point');
        $this->addSql('ALTER TABLE bonif_point DROP id_product');
    }
}
