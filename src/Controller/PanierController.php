<?php

namespace App\Controller;

use App\Repository\TProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/panier')]
class PanierController extends AbstractController
{
    private $tProduitsRepository;
    private $session;
    
    public function __construct(TProduitsRepository $tProduitsRepository, RequestStack $requestStack) {
        $this->tProduitsRepository = $tProduitsRepository;
        $this->session = $requestStack->getSession();
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

    #[Route('', name: 'app_panier')]
    public function index(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
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

        return $this->render('panier/index.html.twig', [
            'produits' => $produitsDetails,
            'totalPanier' => $totalPanier
        ]);
    }

    #[Route('/ajouter', name: 'app_panier_new', methods:['POST'])] 
    public function add(Request $request, SessionInterface $session)
    {
        $produitId = $request->request->get('produit');
        $quantite = $request->request->get('quantite');

        $panier = $session->get('panier', []);

        // Si le produit sélectionné est déjà dans le panier
        if (isset($panier[$produitId])) {
            $panier[$produitId] += $quantite;
        }
        else {
            $panier[$produitId] = $quantite;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('app_panier');
    }

// Méthode pour diminuer la quantité d'un produit dans le panier
// #[Route('/diminuer/{id}', name: 'app_panier_diminuer_ajax', methods:['POST'])]
// public function diminuerQuantiteAjax($id, SessionInterface $session): JsonResponse
// {
//     $panier = $session->get('panier', []);

//     // Vérifie si le produit est présent dans le panier
//     if (isset($panier[$id])) {
//         // Diminue la quantité du produit
//         $panier[$id]--;
//         // Si la quantité atteint zéro, supprime l'élément du panier
//         if ($panier[$id] <= 0) {
//             unset($panier[$id]);
//         }
//         // Met à jour le panier en session
//         $session->set('panier', $panier);
        
//         return new JsonResponse(['success' => true]);
//     }

//     return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé dans le panier.']);
// }

// // Méthode pour augmenter la quantité d'un produit dans le panier
// #[Route('/augmenter/{id}', name: 'app_panier_augmenter_ajax', methods:['POST'])]
// public function augmenterQuantiteAjax($id, SessionInterface $session): JsonResponse
// {
//     $panier = $session->get('panier', []);

//     // Vérifie si le produit est présent dans le panier
//     if (isset($panier[$id])) {
//         // Augmente la quantité du produit
//         $panier[$id]++;
//         // Met à jour le panier en session
//         $session->set('panier', $panier);
        
//         $produit = $this->tProduitsRepository->find($id);

//         $total = $produit->getPrix() * $panier[$id];

//         return new JsonResponse([
//             'success' => true,
//             'quantite' => $panier[$id],
//             'total' => $total,
//             'montantTotal' => $this->montantTotal()
//         ]);
//     }

//     return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé dans le panier.']);
// }

#[Route('/adjust-quantity', name: 'app_panier_adjust_quantity', methods:['POST'])]
public function adjustQuantity(Request $request, SessionInterface $session, TProduitsRepository $tProduitsRepository): JsonResponse
{
    $id = $request->request->get('id');
    $action = $request->request->get('action');

    $panier = $session->get('panier', []);

    // Récupérer le produit depuis le repository
    $produit = $tProduitsRepository->find($id);

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
                $session->set('panier', $panier);
            }

            // Calculer le montant total mis à jour à partir du panier
            $totalPanier = $this->calculateTotalPanier($panier, $tProduitsRepository);

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




// Fonction pour calculer le montant total à partir du panier
private function calculateTotalPanier(array $panier, TProduitsRepository $tProduitsRepository): float
{
    $totalPanier = 0;

    foreach ($panier as $produitId => $quantite) {
        $produit = $tProduitsRepository->find($produitId);
        if ($produit) {
            $totalPanier += $quantite * $produit->getPrix(); // Prix du produit * quantité
        }
    }

    return $totalPanier;
}



    #[Route('/supprimer/{id}', name: 'app_panier_supprimer', methods:['POST'])]
    public function supprimerProduit($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);

        // Vérifie si le produit est présent dans le panier
        if (isset($panier[$id])) {
            // Supprime l'élément du panier
            unset($panier[$id]);
            // Met à jour le panier en session
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('app_panier');
    }

}
