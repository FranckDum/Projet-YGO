<?php

namespace App\Controller;

use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    private $panierService;

    public function __construct(PanierService $panierService) {
        $this->panierService = $panierService;
    }

    #[Route('', name: 'app_panier')]
    public function index(): Response
    {
        // Appel du service pour obtenir les données du panier
        $panierData = $this->panierService->index();
        dump($panierData);
        // Génération de la réponse HTML en utilisant le template approprié
        return $this->render('panier/index.html.twig', $panierData);
    }

    #[Route('/validation_panier', name: 'app_validation_panier')]
    public function validationPanier(Request $request): Response
    {
        // Vérifier l'accès de l'utilisateur
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
    
        // Appel du service pour obtenir les données de validation du panier
        $validationData = $this->panierService->validationPanier($request);
        
        // Vérifier si l'adresse de livraison sélectionnée est invalide
        if (!$validationData['livraison_valide']) {
            // Ajout d'un message flash d'erreur
            $this->addFlash('error', 'L\'adresse de livraison sélectionnée est invalide.');
            // Redirection vers la page de validation du panier
            return $this->redirectToRoute('app_validation_panier');
        }
        
        // Génération de la réponse HTML en utilisant le template approprié
        return $this->render('panier/validation_panier.html.twig', [
            'produits' => $validationData['produitsDetails'],
            'totalPanier' => $validationData['totalPanier'],
            'livreurs' => $validationData['livreurs'],
            'montantTotalCommande' => $validationData['montantTotalCommande'],
            'adressesLivraison' => $validationData['adressesLivraison'],
            'adresseFacturationExistante' => $validationData['adresseFacturationExistante'],
            'user' => $validationData['user'],
        ]);
    }

    #[Route('/get-cart-item-count', name: 'get_cart_item_count')]
    public function getCartItemCount(): Response
    {
        return $this->panierService->getCartItemCount();
    }

    #[Route('/ajouter', name: 'app_panier_new', methods:['POST'])] 
    public function add(Request $request): Response
    {
        // Appel du service pour ajouter un produit au panier
        $response = $this->panierService->add($request);
        
        // Vérification si l'ajout a réussi
        if ($response->isSuccessful()) {
            // Récupération du nom de l'article ajouté
            $articleName = $request->request->get('nomProduit');
            // Ajout d'un message flash de succès
            $this->addFlash('success', 'L\'article "' . $articleName . '" a bien été ajouté au panier');
        } else {
            // Récupération du message d'erreur de la réponse
            $errorMessage = $response->getContent();
            // Ajout d'un message flash d'erreur
            $this->addFlash('error', $errorMessage);
        }

        // Retourne la réponse du service
        return $response;
    }

    #[Route('/adjust-quantity', name: 'app_panier_adjust_quantity', methods:['POST'])]
    public function adjustQuantity(Request $request): Response
    {
        // Appel du service pour obtenir la réponse
        return $this->panierService->adjustQuantity($request);
    }

    #[Route('/supprimer/{id}', name: 'app_panier_supprimer', methods:['POST'])]
    public function supprimerProduit($id): Response
    {
        // Appel du service pour obtenir la réponse
        return $this->panierService->supprimerProduit($id);
    }
}
