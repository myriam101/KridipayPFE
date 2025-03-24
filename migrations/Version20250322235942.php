<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322235942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carbon (id INT AUTO_INCREMENT NOT NULL, id_product INT NOT NULL, date_update DATETIME NOT NULL, date_add DATETIME NOT NULL, value DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_59FACEC4DD7ADDD (id_product), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog (id_catalog INT AUTO_INCREMENT NOT NULL, id_provider INT DEFAULT NULL, public SMALLINT NOT NULL, createdat DATETIME NOT NULL, INDEX IDX_1B2C3247C21F9F09 (id_provider), PRIMARY KEY(id_catalog)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id_category INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, designation VARCHAR(255) NOT NULL, PRIMARY KEY(id_category)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery (id INT AUTO_INCREMENT NOT NULL, id_provider INT DEFAULT NULL, carbon_footprint DOUBLE PRECISION NOT NULL, distance DOUBLE PRECISION NOT NULL, everyday_ride TINYINT(1) NOT NULL, modeliv VARCHAR(255) NOT NULL, client_ride VARCHAR(255) NOT NULL, INDEX IDX_3781EC10C21F9F09 (id_provider), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE energy_bill (id INT AUTO_INCREMENT NOT NULL, id_product INT NOT NULL, price_water_id INT DEFAULT NULL, price_gaz_id INT DEFAULT NULL, price_electricity_id INT DEFAULT NULL, amount_bill DOUBLE PRECISION NOT NULL, amount_gaz DOUBLE PRECISION NOT NULL, amount_electr DOUBLE PRECISION NOT NULL, amount_water DOUBLE PRECISION NOT NULL, bill_category VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FEB15DD4DD7ADDD (id_product), UNIQUE INDEX UNIQ_FEB15DD41597F879 (price_water_id), UNIQUE INDEX UNIQ_FEB15DD4D07A3436 (price_gaz_id), UNIQUE INDEX UNIQ_FEB15DD477B06D4B (price_electricity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feature (id INT AUTO_INCREMENT NOT NULL, weight DOUBLE PRECISION NOT NULL, noise DOUBLE PRECISION NOT NULL, power DOUBLE PRECISION NOT NULL, consumption_liter DOUBLE PRECISION NOT NULL, consumption_watt DOUBLE PRECISION NOT NULL, hdr_consumption DOUBLE PRECISION NOT NULL, sdr_consumption DOUBLE PRECISION NOT NULL, capacity DOUBLE PRECISION NOT NULL, dimension DOUBLE PRECISION NOT NULL, volume_refrigeration DOUBLE PRECISION NOT NULL, volume_freezer DOUBLE PRECISION NOT NULL, volume_collect DOUBLE PRECISION NOT NULL, seer DOUBLE PRECISION NOT NULL, scop DOUBLE PRECISION NOT NULL, cycle_duration DATETIME NOT NULL, nbr_couvert INT NOT NULL, nb_bottle INT NOT NULL, resolution DOUBLE PRECISION NOT NULL, diagonal DOUBLE PRECISION NOT NULL, energy_class VARCHAR(255) NOT NULL, condens_perform VARCHAR(255) NOT NULL, spindry_class VARCHAR(255) NOT NULL, steam_class VARCHAR(255) NOT NULL, light_class VARCHAR(255) NOT NULL, filtre_class VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_electricity (id INT AUTO_INCREMENT NOT NULL, tax DOUBLE PRECISION NOT NULL, price_day DOUBLE PRECISION NOT NULL, price_night DOUBLE PRECISION NOT NULL, price_rush DOUBLE PRECISION NOT NULL, periode_use VARCHAR(255) NOT NULL, sector VARCHAR(255) NOT NULL, tranche_elect VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_gaz (id INT AUTO_INCREMENT NOT NULL, pressure TINYINT(1) NOT NULL, price DOUBLE PRECISION NOT NULL, tranche_gaz VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_water (id INT AUTO_INCREMENT NOT NULL, price DOUBLE PRECISION NOT NULL, tranche_eau VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, id_category INT DEFAULT NULL, id_catalog INT DEFAULT NULL, name LONGTEXT NOT NULL, description LONGTEXT NOT NULL, short_description VARCHAR(150) DEFAULT NULL, reference LONGTEXT NOT NULL, brand LONGTEXT NOT NULL, bonifpoint INT NOT NULL, INDEX IDX_D34A04AD5697F554 (id_category), INDEX IDX_D34A04ADC5B19B37 (id_catalog), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE provider (id_provider INT AUTO_INCREMENT NOT NULL, adress VARCHAR(128) NOT NULL, PRIMARY KEY(id_provider)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE simulation (id INT AUTO_INCREMENT NOT NULL, id_product INT NOT NULL, duration_use DATETIME NOT NULL, nbr_use INT NOT NULL, hour_use DATETIME NOT NULL, UNIQUE INDEX UNIQ_CBDA467BDD7ADDD (id_product), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carbon ADD CONSTRAINT FK_59FACEC4DD7ADDD FOREIGN KEY (id_product) REFERENCES product (id)');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247C21F9F09 FOREIGN KEY (id_provider) REFERENCES provider (id_provider)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10C21F9F09 FOREIGN KEY (id_provider) REFERENCES provider (id_provider)');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD4DD7ADDD FOREIGN KEY (id_product) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD41597F879 FOREIGN KEY (price_water_id) REFERENCES price_water (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD4D07A3436 FOREIGN KEY (price_gaz_id) REFERENCES price_gaz (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD477B06D4B FOREIGN KEY (price_electricity_id) REFERENCES price_electricity (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD5697F554 FOREIGN KEY (id_category) REFERENCES category (id_category)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC5B19B37 FOREIGN KEY (id_catalog) REFERENCES catalog (id_catalog)');
        $this->addSql('ALTER TABLE simulation ADD CONSTRAINT FK_CBDA467BDD7ADDD FOREIGN KEY (id_product) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carbon DROP FOREIGN KEY FK_59FACEC4DD7ADDD');
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247C21F9F09');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10C21F9F09');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD4DD7ADDD');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD41597F879');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD4D07A3436');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD477B06D4B');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD5697F554');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC5B19B37');
        $this->addSql('ALTER TABLE simulation DROP FOREIGN KEY FK_CBDA467BDD7ADDD');
        $this->addSql('DROP TABLE carbon');
        $this->addSql('DROP TABLE catalog');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE energy_bill');
        $this->addSql('DROP TABLE feature');
        $this->addSql('DROP TABLE price_electricity');
        $this->addSql('DROP TABLE price_gaz');
        $this->addSql('DROP TABLE price_water');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE provider');
        $this->addSql('DROP TABLE simulation');
    }
}
