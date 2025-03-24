<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324093253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bonif_point (id INT AUTO_INCREMENT NOT NULL, id_client INT DEFAULT NULL, date_win DATETIME NOT NULL, date_use DATETIME NOT NULL, nbr_pt INT NOT NULL, type_point VARCHAR(255) NOT NULL, INDEX IDX_7CEFE78AE173B1B8 (id_client), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, id_client INT DEFAULT NULL, INDEX IDX_BA388B7E173B1B8 (id_client), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_container (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, cart_id INT DEFAULT NULL, INDEX IDX_1D5CCB844584665A (product_id), INDEX IDX_1D5CCB841AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, adress LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bonif_point ADD CONSTRAINT FK_7CEFE78AE173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7E173B1B8 FOREIGN KEY (id_client) REFERENCES client (id)');
        $this->addSql('ALTER TABLE cart_container ADD CONSTRAINT FK_1D5CCB844584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE cart_container ADD CONSTRAINT FK_1D5CCB841AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE carbon ADD visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE feature ADD id_product INT NOT NULL');
        $this->addSql('ALTER TABLE feature ADD CONSTRAINT FK_1FD77566DD7ADDD FOREIGN KEY (id_product) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FD77566DD7ADDD ON feature (id_product)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bonif_point DROP FOREIGN KEY FK_7CEFE78AE173B1B8');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7E173B1B8');
        $this->addSql('ALTER TABLE cart_container DROP FOREIGN KEY FK_1D5CCB844584665A');
        $this->addSql('ALTER TABLE cart_container DROP FOREIGN KEY FK_1D5CCB841AD5CDBF');
        $this->addSql('DROP TABLE bonif_point');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_container');
        $this->addSql('DROP TABLE client');
        $this->addSql('ALTER TABLE carbon DROP visible');
        $this->addSql('ALTER TABLE feature DROP FOREIGN KEY FK_1FD77566DD7ADDD');
        $this->addSql('DROP INDEX UNIQ_1FD77566DD7ADDD ON feature');
        $this->addSql('ALTER TABLE feature DROP id_product');
    }
}
