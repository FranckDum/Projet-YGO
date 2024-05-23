<?php

namespace App\Entity;

use App\Repository\CommandesRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandesRepository::class)]
class Commandes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_commande = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'commandes', targetEntity: DetailCommande::class, cascade:['persist', 'remove'])]
    private Collection $detailCommande;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeros = null;

    #[ORM\ManyToOne(inversedBy: 'commandes', targetEntity: Livraison::class, cascade: ['persist'])]
    private ?Livraison $livraison = null;

    #[ORM\OneToOne(mappedBy: 'commandes', cascade: ['persist', 'remove'])]
    private ?AdresseLivraisonCommande $adresseLivraisonCommande = null;

    #[ORM\OneToOne(mappedBy: 'commandes', cascade: ['persist', 'remove'])]
    private ?AdresseFacturationCommande $adresseFacturationCommande = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant_total = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripe_id = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->date_commande = new \DateTimeImmutable();
    }

    public function __construct()
    {
        $this->detailCommande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->date_commande;
    }

    public function setDateCommande(?\DateTimeImmutable $date_commande): static
    {
        $this->date_commande = $date_commande;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $detailCommande->setCommandes($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommande->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getCommandes() === $this) {
                $detailCommande->setCommandes(null);
            }
        }

        return $this;
    }

    public function getNumeros(): ?string
    {
        return $this->numeros;
    }

    public function setNumeros(?string $numeros): static
    {
        $this->numeros = $numeros;

        return $this;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }

    public function getAdresseLivraisonCommande(): ?AdresseLivraisonCommande
    {
        return $this->adresseLivraisonCommande;
    }

    public function setAdresseLivraisonCommande(?AdresseLivraisonCommande $adresseLivraisonCommande): static
    {
        // unset the owning side of the relation if necessary
        if ($adresseLivraisonCommande === null && $this->adresseLivraisonCommande !== null) {
            $this->adresseLivraisonCommande->setCommandes(null);
        }

        // set the owning side of the relation if necessary
        if ($adresseLivraisonCommande !== null && $adresseLivraisonCommande->getCommandes() !== $this) {
            $adresseLivraisonCommande->setCommandes($this);
        }

        $this->adresseLivraisonCommande = $adresseLivraisonCommande;

        return $this;
    }

    public function getAdresseFacturationCommande(): ?AdresseFacturationCommande
    {
        return $this->adresseFacturationCommande;
    }

    public function setAdresseFacturationCommande(?AdresseFacturationCommande $adresseFacturationCommande): static
    {
        // unset the owning side of the relation if necessary
        if ($adresseFacturationCommande === null && $this->adresseFacturationCommande !== null) {
            $this->adresseFacturationCommande->setCommandes(null);
        }

        // set the owning side of the relation if necessary
        if ($adresseFacturationCommande !== null && $adresseFacturationCommande->getCommandes() !== $this) {
            $adresseFacturationCommande->setCommandes($this);
        }

        $this->adresseFacturationCommande = $adresseFacturationCommande;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montant_total;
    }

    public function setMontantTotal(?float $montant_total): static
    {
        $this->montant_total = $montant_total;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripe_id;
    }

    public function setStripeId(?string $stripe_id): static
    {
        $this->stripe_id = $stripe_id;

        return $this;
    }

}
