<?php

namespace App\Entity;

use App\Repository\AdresseLivraisonCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresseLivraisonCommandeRepository::class)]
class AdresseLivraisonCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse_livraison_commande = null;

    #[ORM\OneToOne(inversedBy: 'adresseLivraisonCommande', cascade: ['persist', 'remove'])]
    private ?Commandes $commandes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $complement_adresse = null;

    #[ORM\Column(nullable: true)]
    private ?float $cp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseLivraisonCommande(): ?string
    {
        return $this->adresse_livraison_commande;
    }

    public function setAdresseLivraisonCommande(?string $adresse_livraison_commande): static
    {
        $this->adresse_livraison_commande = $adresse_livraison_commande;

        return $this;
    }

    public function getCommandes(): ?Commandes
    {
        return $this->commandes;
    }

    public function setCommandes(?Commandes $commandes): static
    {
        $this->commandes = $commandes;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getComplementAdresse(): ?string
    {
        return $this->complement_adresse;
    }

    public function setComplementAdresse(?string $complement_adresse): static
    {
        $this->complement_adresse = $complement_adresse;

        return $this;
    }

    public function getCp(): ?float
    {
        return $this->cp;
    }

    public function setCp(?float $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }
}
