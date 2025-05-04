<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503174207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_catalog (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, id_catalog INT DEFAULT NULL, INDEX IDX_CAF529F74584665A (product_id), INDEX IDX_CAF529F7C5B19B37 (id_catalog), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_catalog ADD CONSTRAINT FK_CAF529F74584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_catalog ADD CONSTRAINT FK_CAF529F7C5B19B37 FOREIGN KEY (id_catalog) REFERENCES catalog (id_catalog)');
        $this->addSql('ALTER TABLE productcatalog DROP FOREIGN KEY FK_806394604584665A');
        $this->addSql('ALTER TABLE productcatalog DROP FOREIGN KEY FK_80639460C5B19B37');
        $this->addSql('DROP TABLE productcatalog');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC21F9F09');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC21F9F09 FOREIGN KEY (id_provider) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(255) NOT NULL, CHANGE username username VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE productcatalog (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, id_catalog INT DEFAULT NULL, INDEX IDX_806394604584665A (product_id), INDEX IDX_80639460C5B19B37 (id_catalog), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE productcatalog ADD CONSTRAINT FK_806394604584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE productcatalog ADD CONSTRAINT FK_80639460C5B19B37 FOREIGN KEY (id_catalog) REFERENCES catalog (id_catalog) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE product_catalog DROP FOREIGN KEY FK_CAF529F74584665A');
        $this->addSql('ALTER TABLE product_catalog DROP FOREIGN KEY FK_CAF529F7C5B19B37');
        $this->addSql('DROP TABLE product_catalog');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC21F9F09');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC21F9F09 FOREIGN KEY (id_provider) REFERENCES provider (id_provider) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE user CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE username username VARCHAR(255) DEFAULT NULL');
    }
}
