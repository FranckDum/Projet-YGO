<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240319095924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE adresse adresse VARCHAR(100) DEFAULT NULL, CHANGE tel tel VARCHAR(14) DEFAULT NULL, CHANGE ville ville VARCHAR(100) DEFAULT NULL, CHANGE code_postal code_postal DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE adresse adresse VARCHAR(100) NOT NULL, CHANGE tel tel VARCHAR(14) NOT NULL, CHANGE ville ville VARCHAR(100) NOT NULL, CHANGE code_postal code_postal DOUBLE PRECISION NOT NULL');
    }
}
