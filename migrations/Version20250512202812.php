<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512202812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price_electricity DROP tax, DROP price_day, DROP price_night, DROP price_rush');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price_electricity ADD tax DOUBLE PRECISION NOT NULL, ADD price_day DOUBLE PRECISION NOT NULL, ADD price_night DOUBLE PRECISION NOT NULL, ADD price_rush DOUBLE PRECISION NOT NULL');
    }
}
