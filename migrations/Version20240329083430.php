<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240329083430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commandes (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, date_livraison DATE DEFAULT NULL, statut VARCHAR(255) DEFAULT NULL, facture VARCHAR(255) DEFAULT NULL, INDEX IDX_35D4282CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE detail_commande (id INT AUTO_INCREMENT NOT NULL, commandes_id INT DEFAULT NULL, t_produits_id INT DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, quantity INT DEFAULT NULL, INDEX IDX_98344FA68BF5C2E6 (commandes_id), INDEX IDX_98344FA69D4086CE (t_produits_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commandes ADD CONSTRAINT FK_35D4282CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA68BF5C2E6 FOREIGN KEY (commandes_id) REFERENCES commandes (id)');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA69D4086CE FOREIGN KEY (t_produits_id) REFERENCES tproduits (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL COMMENT \'(DC2Type:json)\', ADD password VARCHAR(255) NOT NULL, DROP mdp, CHANGE date_naissance date_naissance DATE DEFAULT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE adresse adresse VARCHAR(100) DEFAULT NULL, CHANGE tel tel VARCHAR(14) DEFAULT NULL, CHANGE ville ville VARCHAR(100) DEFAULT NULL, CHANGE code_postal code_postal DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commandes DROP FOREIGN KEY FK_35D4282CA76ED395');
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA68BF5C2E6');
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA69D4086CE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE commandes');
        $this->addSql('DROP TABLE detail_commande');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD mdp VARCHAR(50) NOT NULL, DROP roles, DROP password, CHANGE email email VARCHAR(255) NOT NULL, CHANGE date_naissance date_naissance DATE NOT NULL, CHANGE adresse adresse VARCHAR(255) NOT NULL, CHANGE tel tel VARCHAR(16) NOT NULL, CHANGE ville ville VARCHAR(100) NOT NULL, CHANGE code_postal code_postal INT NOT NULL');
    }
}
