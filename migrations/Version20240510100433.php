<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240510100433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse_livraison ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adresse_livraison ADD CONSTRAINT FK_B0B09C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B0B09C9A76ED395 ON adresse_livraison (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse_livraison DROP FOREIGN KEY FK_B0B09C9A76ED395');
        $this->addSql('DROP INDEX IDX_B0B09C9A76ED395 ON adresse_livraison');
        $this->addSql('ALTER TABLE adresse_livraison DROP user_id');
    }
}
