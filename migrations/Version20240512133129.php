<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240512133129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse_livraison_commande (id INT AUTO_INCREMENT NOT NULL, commandes_id INT DEFAULT NULL, adresse_livraison_commande VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_23C1E91C8BF5C2E6 (commandes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adresse_livraison_commande ADD CONSTRAINT FK_23C1E91C8BF5C2E6 FOREIGN KEY (commandes_id) REFERENCES commandes (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse_livraison_commande DROP FOREIGN KEY FK_23C1E91C8BF5C2E6');
        $this->addSql('DROP TABLE adresse_livraison_commande');
    }
}
