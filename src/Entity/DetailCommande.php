<?php

namespace App\Entity;

use App\Repository\DetailCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailCommandeRepository::class)]
class DetailCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'detailCommande')]
    private ?Commandes $commandes = null;

    #[ORM\ManyToOne(inversedBy: 'detailCommande')]
    private ?TProduits $tProduits = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

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

    public function getTProduits(): ?TProduits
    {
        return $this->tProduits;
    }

    public function setTProduits(?TProduits $tProduits): static
    {
        $this->tProduits = $tProduits;

        return $this;
    }
}
