<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504134241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247C21F9F09');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247C21F9F09 FOREIGN KEY (id_provider) REFERENCES user (id)');
        $this->addSql('ALTER TABLE feature DROP condens_perform, DROP spindry_class, DROP steam_class, DROP light_class, DROP filtre_class');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247C21F9F09');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247C21F9F09 FOREIGN KEY (id_provider) REFERENCES provider (id_provider) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE feature ADD condens_perform VARCHAR(255) NOT NULL, ADD spindry_class VARCHAR(255) NOT NULL, ADD steam_class VARCHAR(255) NOT NULL, ADD light_class VARCHAR(255) NOT NULL, ADD filtre_class VARCHAR(255) NOT NULL');
    }
}
