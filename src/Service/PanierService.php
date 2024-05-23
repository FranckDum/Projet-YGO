<?php

namespace App\Service;

use App\Entity\AdresseLivraison;
use App\Entity\AdresseFacturation;
use App\Repository\LivraisonRepository;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierService
{
    private $tProduitsRepository;
    private $session;
    private $livreurs;
    private $em;
    private $tokenStorage;
    private $notificationManager;

    public function __construct(TProduitsRepository $tProduitsRepository, LivraisonRepository $livreurs, RequestStack $requestStack, EntityManagerInterface $em, TokenStorageInterface $tokenStorage, NotificationManagerService $notificationManager) {
        $this->tProduitsRepository = $tProduitsRepository;
        $this->session = $requestStack->getSession();
        $this->livreurs = $livreurs;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->notificationManager = $notificationManager;
    }

    public function getStructurePanier()
    {
        return [
            'data' => [],
            'livraison' => null,
            'rupture' => []
        ];
    }

    public function getPanier()
    {
        $panier = $this->session->get('panier', []);

        if (!$panier) {
            $this->session->set('panier', $this->getStructurePanier());
        }
        return $this->session->get('panier', []);
    }

    public function getQuantitePanier(): int
    {
        $quantite = 0;
        $panier = $this->getPanier();

        if($panier['data']) {
            foreach ($panier['data'] as $value) {
                $quantite += $value;
            }
        }


        return $quantite;
    }

    public function montantTotal() : float
    {
        $panier = $this->session->get('panier', []);
        $totalPanier = 0; // Initialiser le total à zéro

        // Récupérer les détails des produits présents dans le panier
        foreach ($panier as $produitId => $quantite) {
            $produit = $this->tProduitsRepository->find($produitId);

            // Si le produit existe, ajoutez-le à la liste des détails de produit
            if ($produit) {

            // Ajouter le prix du produit au total
            $totalPanier += $quantite * $produit->getPrix();
            }
        }
        

        return $totalPanier;
    }

    public function index(): array
    {
        $panier = $this->session->get('panier', []);
        $produitsDetails = [];
        $totalPanier = 0; // Initialiser le total à zéro

        // Récupérer les détails des produits présents dans le panier
        foreach ($panier as $produitId => $quantite) {
            $produit = $this->tProduitsRepository->find($produitId);

            // Si le produit existe, ajoutez-le à la liste des détails de produit
            if ($produit) {
                $produitsDetails[] = [
                    'id' => $produit->getId(),
                    'ygoId' => $produit->getYgoId(),
                    'nom' => $produit->getNomProduit(),
                    'quantite' => $quantite,
                    'prix' => $produit->getPrix(),
                    'total' => $quantite * $produit->getPrix(),
                ];

            // Ajouter le prix du produit au total
            $totalPanier += $quantite * $produit->getPrix();
            }
        }

        // Retourner les détails du panier
        return [
            'produits' => $produitsDetails,
            'totalPanier' => $totalPanier
        ];
    }

    public function validationPanier(Request $request): array
    {
    
        $panier = $this->session->get('panier', []);
        $produitsDetails = [];
        $totalPanier = 0;
    
        // Récupérer les détails des produits présents dans le panier
        foreach ($panier as $produitId => $quantite) {
            $produit = $this->tProduitsRepository->find($produitId);
    
            // Si le produit existe, ajoutez-le à la liste des détails de produit
            if ($produit) {
                $produitsDetails[] = [
                    'id' => $produit->getId(),
                    'ygoId' => $produit->getYgoId(),
                    'nom' => $produit->getNomProduit(),
                    'quantite' => $quantite,
                    'prix' => $produit->getPrix(),
                    'total' => $quantite * $produit->getPrix(),
                ];
    
                // Ajouter le prix du produit au total
                $totalPanier += $quantite * $produit->getPrix();
            }
        }
    
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->tokenStorage->getToken()->getUser();
        $this->session->set('user', $user);
        // Récupérer l'adresse de facturation existante de l'utilisateur s'il en a une
        $adresseFacturationExistante = $this->em->getRepository(AdresseFacturation::class)->findOneBy(['user' => $user]);
        // Récupérer les adresses de livraison existantes de l'utilisateur
        $adressesLivraison = $this->em->getRepository(AdresseLivraison::class)->findBy(['user' => $user]);
    
        // Stocker le montant total de la commande dans la session
        $this->session->set('adresse_facturation_id', $adresseFacturationExistante);
        $montantTotalCommande = $totalPanier;
        $livreurs = $this->livreurs->findAll();
    
        // Traiter les sélections de livreur et d'adresse de livraison
        if ($request->isMethod('POST')) {
            $livreurId = $request->request->get('livreur');
            $adresseLivraisonId = $request->request->get('adresse_livraison');
    
            $livreur = $this->livreurs->find($livreurId);
            $livraisonUser = $this->em->getRepository(AdresseLivraison::class)->find($adresseLivraisonId);
    
            if ($livreur !== null) {
                // Ajouter les frais de livraison au montant total de la commande
                $montantTotalCommande += $livreur->getPrix();
            // Stocker le mode de livraison dans la session
        }
        $this->session->set('mode_livraison', $livreurId);
    
        // Vérifier si l'adresse de livraison sélectionnée existe
        if ($livraisonUser !== null) {
            // Stocker l'adresse de livraison sélectionnée dans la session
            $this->session->set('adresse_livraison_id', $adresseLivraisonId);
        } else {
            // Gérer le cas où l'adresse de livraison est invalide
            return [
                'livraison_valide' => false, // Indiquer que l'adresse de livraison est invalide
                // Autres données nécessaires pour générer la réponse
            ];
        }
    
        // Stocker le montant total de la commande dans la session
        $this->session->set('montantTotalCommande', $montantTotalCommande);
    }
    
    // Retourner les données de validation
    return [
        'livraison_valide' => true, // Indiquer que l'adresse de livraison est valide
        'produitsDetails' => $produitsDetails,
        'totalPanier' => $totalPanier,
        'livreurs' => $livreurs,
        'montantTotalCommande' => $montantTotalCommande,
        'adressesLivraison' => $adressesLivraison,
        'adresseFacturationExistante' => $adresseFacturationExistante,
        'user' => $user
    ];
    }
    

    public function getCartItemCount(): JsonResponse
    {
        // $panier = $this->session->get('panier', []);
        // $totalItems = array_sum($panier); // Somme de toutes les quantités dans le panier
        // return new JsonResponse(['itemCount' => $totalItems]);
        $itemCount = $this->notificationManager->getQuantitePanier();
        return new JsonResponse(['itemCount' => $itemCount]);
    }

    public function add(Request $request): Response
    {
        $produitId = $request->request->get('produit');
        $quantite =  $request->request->get('quantite');
    
        $panier = $this->session->get('panier', []);
    
        // Vérification de la quantité maximale autorisée dans le panier (10)
        if (isset($panier[$produitId])) {
            $quantitePanier = $panier[$produitId];
            if ($quantitePanier >= 10) {
                // Retourner une réponse JSON avec un message d'erreur
                return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas ajouter plus de 10 fois le même article par commande.']);
            } else if ($quantitePanier + $quantite > 10) {
                // Retourner une réponse JSON avec un message d'erreur
                return new JsonResponse(['success' => false, 'message' => 'Vous ne pouvez pas ajouter plus de 10 fois le même article par commande.']);
            }
        }
    
        // Ajout du produit au panier
        if (isset($panier[$produitId])) {
            // Si le produit existe déjà dans le panier, mettre à jour la quantité
            $panier[$produitId] += $quantite;
        } else {
            // Sinon, ajouter le produit au panier avec la quantité spécifiée
            $panier[$produitId] = $quantite;
        }
    
        $this->session->set('panier', $panier);
    
        // Ajout d'un message flash
        $articleName = $request->request->get('nomProduit');
    
        // Retourne une réponse JSON pour confirmer l'ajout
        return new JsonResponse(['success' => true, 'message' => 'Le produit a bien été ajouté au panier.']);
    }

    public function verification($produitId, $quantite)
    {
        $stock = 10; // Par exemple, la quantité en stock pour le produit $produitId est de 10
    
        // Récupère le panier actuel
        $panier = $this->session->get('panier', []);
    
        // Si le produit est déjà dans le panier
        if (isset($panier[$produitId])) {
            // Calcule la quantité totale du produit dans le panier
            $quantite_totale = $panier[$produitId] + $quantite;
    
            // Vérifie si la quantité totale dépasse le stock disponible ou si elle dépasse 10
            if ($quantite_totale > $stock || $quantite_totale > 10) {
                // Retourne false si la quantité dépasse le stock ou dépasse 10
                return false;
            }
        }
    
        // Retourne true si la quantité est dans le stock ou si le produit n'est pas encore dans le panier
        return true;
    }

    public function adjustQuantity(Request $request): JsonResponse
    {
        $id = $request->request->get('id');
        $action = $request->request->get('action');

        $panier = $this->session->get('panier', []);

        // Récupérer le produit depuis le repository
        $produit = $this->tProduitsRepository->find($id);

        if ($produit) {
            // Sauvegarder la quantité actuelle dans le panier
            $ancienneQuantite = $panier[$id] ?? 0;

            // Vérifier si la quantité dans le panier est un nombre valide
            if (!is_numeric($ancienneQuantite)) {
                $ancienneQuantite = 0; // Utiliser 0 si la quantité n'est pas valide
            }

            $stockDisponible = $produit->getStock();

            // Vérifier si le stock disponible est un nombre valide
            if (!is_numeric($stockDisponible)) {
                return new JsonResponse(['success' => false, 'message' => 'Stock invalide.']);
            }

            if ($stockDisponible !== null) {
                if ($action === 'decrement' && isset($panier[$id])) {
                    // Diminuer la quantité dans le panier
                    $panier[$id] = max(0, $panier[$id] - 1);
                } elseif ($action === 'increment') {
                    // Vérifier si la quantité demandée dépasse le stock disponible
                    if ($panier[$id] + 1 <= $stockDisponible) {
                        // Augmenter la quantité dans le panier
                        $panier[$id]++;
                    } else {
                        // Retourner une erreur si la quantité demandée dépasse le stock disponible
                        return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant pour cette quantité.']);
                    }
                }

                // Mettre à jour le panier en session seulement si la quantité a été modifiée avec succès
                if ($ancienneQuantite !== ($panier[$id] ?? 0)) {
                    $this->session->set('panier', $panier);
                }

                // Calculer le montant total mis à jour à partir du panier
                $totalPanier = $this->calculateTotalPanier($panier, $this->tProduitsRepository);

                // Calculer le total pour ce produit
                $totalProduit = ($panier[$id] ?? 0) * $produit->getPrix();

                // Formater les valeurs à deux chiffres après la virgule
                $totalProduitFormatted = number_format($totalProduit, 2);

                // Retourner la nouvelle quantité et le nouveau montant total dans la réponse JSON
                return new JsonResponse([
                    'success' => true,
                    'quantity' => $panier[$id] ?? 0, // Utiliser la nouvelle quantité si elle existe, sinon utiliser 0
                    'total' => $totalProduitFormatted, // Prix du produit * quantité
                    'montantTotal' => number_format($totalPanier, 2)
                ]);
            }
        }

        return new JsonResponse(['success' => false, 'message' => 'Le produit n\'a pas été trouvé ou le stock n\'est pas défini.']);
    }

    private function calculateTotalPanier(array $panier): float
    {
        $totalPanier = 0;

        foreach ($panier as $produitId => $quantite) {
            $produit = $this->tProduitsRepository->find($produitId);
            if ($produit) {
                $totalPanier += $quantite * $produit->getPrix(); // Prix du produit * quantité
            }
        }

        return $totalPanier;
    }

    public function supprimerProduit($id)
    {
        $panier = $this->session->get('panier', []);

        // Vérifie si le produit est présent dans le panier
        if (isset($panier[$id])) {
            // Récupère le nom de l'article avant de le supprimer
            $produit = $this->tProduitsRepository->find($id);
            $articleName = $produit ? $produit->getNomProduit() : '';

            // Supprime l'élément du panier
            unset($panier[$id]);
            // Met à jour le panier en session
            $this->session->set('panier', $panier);

            // Récupérer le nouveau montant total
            $totalPanier = $this->calculateTotalPanier($panier, $this->tProduitsRepository);

            // Retourne une réponse JSON avec le succès et le nouveau montant total
            return new JsonResponse(['success' => true, 'articleName' => $articleName, 'totalPanier' => $totalPanier]);
        }

        // Retourne une réponse JSON indiquant l'échec si le produit n'est pas trouvé dans le panier
        return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé dans le panier.']);
    }
}