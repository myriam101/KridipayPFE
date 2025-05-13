<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512212609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE energy_bill DROP INDEX UNIQ_FEB15DD41597F879, ADD INDEX IDX_FEB15DD41597F879 (price_water_id)');
        $this->addSql('ALTER TABLE energy_bill DROP INDEX UNIQ_FEB15DD477B06D4B, ADD INDEX IDX_FEB15DD477B06D4B (price_electricity_id)');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD41597F879');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD477B06D4B');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD41597F879 FOREIGN KEY (price_water_id) REFERENCES price_water (id)');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD477B06D4B FOREIGN KEY (price_electricity_id) REFERENCES price_electricity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE energy_bill DROP INDEX IDX_FEB15DD477B06D4B, ADD UNIQUE INDEX UNIQ_FEB15DD477B06D4B (price_electricity_id)');
        $this->addSql('ALTER TABLE energy_bill DROP INDEX IDX_FEB15DD41597F879, ADD UNIQUE INDEX UNIQ_FEB15DD41597F879 (price_water_id)');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD477B06D4B');
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD41597F879');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD477B06D4B FOREIGN KEY (price_electricity_id) REFERENCES price_electricity (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD41597F879 FOREIGN KEY (price_water_id) REFERENCES price_water (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
