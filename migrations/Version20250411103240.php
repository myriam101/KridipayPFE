<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411103240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE simulation DROP INDEX UNIQ_CBDA467BDD7ADDD, ADD INDEX IDX_CBDA467BDD7ADDD (id_product)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE simulation DROP INDEX IDX_CBDA467BDD7ADDD, ADD UNIQUE INDEX UNIQ_CBDA467BDD7ADDD (id_product)');
    }
}
