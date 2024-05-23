<?php

namespace App\Entity;

use App\Repository\TProduitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#[ORM\UniqueConstraint(columns:['nom_produit'])]
#[ORM\Entity(repositoryClass: TProduitsRepository::class)]
// Annotation indiquant que cette classe est une entité Doctrine et spécifiant la classe du référentiel associé
class TProduits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    // Annotations définissant la clé primaire, auto-incrémentée, et le type de colonne pour l'identifiant

    #[ORM\Column]
    private ?bool $activation = null;
    // Annotation définissant une colonne de type BOOLEAN pour l'activation

    #[ORM\Column]
    private ?int $stock = null;
    // Annotation définissant une colonne de type INTEGER pour le stock

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;
    // Annotation définissant une colonne de type FLOAT pour le prix, autorisant la valeur null

    #[ORM\Column(length: 255)]
    private ?string $nom_produit = null;
    // Annotation définissant une colonne de type VARCHAR avec une longueur maximale de 255 caractères pour le nom du produit

    #[ORM\Column]
    private ?int $ygo_id = null;

    #[ORM\OneToMany(mappedBy: 'tProduits', targetEntity: DetailCommande::class)]
    private Collection $detailCommande;

    public function __construct()
    {
        $this->detailCommande = new ArrayCollection();
    }
    // Annotation définissant une colonne de type INTEGER pour l'identifiant YGO

    // Méthodes getters et setters pour accéder et modifier les propriétés de l'entité

    public function isActivation(): ?bool
    {
        return $this->activation;
    }

    public function setActivation(bool $activation): static
    {
        $this->activation = $activation;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
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

    public function getNomProduit(): ?string
    {
        return $this->nom_produit;
    }

    public function setNomProduit(string $nom_produit): static
    {
        $this->nom_produit = $nom_produit;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Méthodes similaires pour chaque propriété...

    public function getYgoId(): ?int
    {
        return $this->ygo_id;
    }

    public function setYgoId(int $ygo_id): static
    {
        $this->ygo_id = $ygo_id;

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getDetailCommande(): Collection
    {
        return $this->detailCommande;
    }

    public function addDetailCommande(DetailCommande $detailCommande): static
    {
        if (!$this->detailCommande->contains($detailCommande)) {
            $this->detailCommande->add($detailCommande);
            $detailCommande->setTProduits($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommande->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getTProduits() === $this) {
                $detailCommande->setTProduits(null);
            }
        }

        return $this;
    }
}
