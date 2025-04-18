<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411151822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD4DD7ADDD');
        $this->addSql('DROP INDEX UNIQ_FEB15DD4DD7ADDD ON energy_bill');
        $this->addSql('ALTER TABLE energy_bill CHANGE id_product simulation_id INT NOT NULL');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD4FEC09103 FOREIGN KEY (simulation_id) REFERENCES simulation (id)');
        $this->addSql('CREATE INDEX IDX_FEB15DD4FEC09103 ON energy_bill (simulation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE energy_bill DROP FOREIGN KEY FK_FEB15DD4FEC09103');
        $this->addSql('DROP INDEX IDX_FEB15DD4FEC09103 ON energy_bill');
        $this->addSql('ALTER TABLE energy_bill CHANGE simulation_id id_product INT NOT NULL');
        $this->addSql('ALTER TABLE energy_bill ADD CONSTRAINT FK_FEB15DD4DD7ADDD FOREIGN KEY (id_product) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEB15DD4DD7ADDD ON energy_bill (id_product)');
    }
}
