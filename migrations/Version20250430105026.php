<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430105026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE productcatalog (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, id_catalog INT DEFAULT NULL, INDEX IDX_806394604584665A (product_id), INDEX IDX_80639460C5B19B37 (id_catalog), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE productcatalog ADD CONSTRAINT FK_806394604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE productcatalog ADD CONSTRAINT FK_80639460C5B19B37 FOREIGN KEY (id_catalog) REFERENCES catalog (id_catalog)');
        $this->addSql('ALTER TABLE carbon DROP factor');
        $this->addSql('ALTER TABLE catalog ADD name LONGTEXT NOT NULL, CHANGE public public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product ADD id_provider INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC21F9F09 FOREIGN KEY (id_provider) REFERENCES provider (id_provider)');
        $this->addSql('CREATE INDEX IDX_D34A04ADC21F9F09 ON product (id_provider)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE productcatalog DROP FOREIGN KEY FK_806394604584665A');
        $this->addSql('ALTER TABLE productcatalog DROP FOREIGN KEY FK_80639460C5B19B37');
        $this->addSql('DROP TABLE productcatalog');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE carbon ADD factor DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE catalog DROP name, CHANGE public public SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC21F9F09');
        $this->addSql('DROP INDEX IDX_D34A04ADC21F9F09 ON product');
        $this->addSql('ALTER TABLE product DROP id_provider');
    }
}
