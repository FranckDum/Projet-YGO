<?php

namespace App\Controller;

use App\Entity\Commandes;
use App\Entity\TProduits;
use App\Form\CommandesType;
use App\Repository\CommandesRepository;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/commandes')]
class CommandesController extends AbstractController
{
    private $tProduitsRepository;
    private $session;

    public function __construct(TProduitsRepository $tProduitsRepository, RequestStack $requestStack) {
        $this->tProduitsRepository = $tProduitsRepository;
        $this->session = $requestStack->getSession();
        
    }
    #[Route('/', name: 'app_commandes_index', methods: ['GET'])]
    public function index(CommandesRepository $commandesRepository): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $commandes = $commandesRepository->findAll();
        } else {
            $commandes = $commandesRepository->findBy(['user' => $user]);
        }

        return $this->render('commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    // #[Route('/new', name: 'app_commandes_new', methods: ['GET', 'POST'])]
    // public function new(Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $panier = $this->session->get('panier', []);
    //     $produitsDetails = [];
    //     $totalPanier = 0; // Initialiser le total à zéro

    //     // Récupérer les détails des produits présents dans le panier
    //     foreach ($panier as $produitId => $quantite) {
    //         $produit = $this->tProduitsRepository->find($produitId);

    //         // Si le produit existe, ajoutez-le à la liste des détails de produit
    //         if ($produit) {
    //             $produitsDetails[] = [
    //                 'id' => $produit->getId(),
    //                 'ygoId' => $produit->getYgoId(),
    //                 'nom' => $produit->getNomProduit(),
    //                 'quantite' => $quantite,
    //                 'prix' => $produit->getPrix(),
    //                 'total' => $quantite * $produit->getPrix(),
    //             ];

    //         // Ajouter le prix du produit au total
    //         $totalPanier += $quantite * $produit->getPrix();
    //         }
    //     }

    //     $commande = new Commandes();
    //     $form = $this->createForm(CommandesType::class, $commande);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->persist($commande);
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_commandes_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('commandes/new.html.twig', [
    //         'commande' => $commande,
    //         'form' => $form,
    //         'produits' => $produitsDetails,
    //         'totalPanier' => $totalPanier
    //     ]);
    // }

    #[Route('/new', name: 'app_commandes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les informations de la session
        $commandeInfo = $this->session->get('commande_info', []);
        
        // Ici, vous devriez récupérer les détails des produits associés à la commande
        // Cela dépend de la structure de votre application et de la manière dont vous stockez ces détails dans la session
        // Par exemple, vous pouvez utiliser l'identifiant du produit pour récupérer les détails depuis la base de données
        // Assurez-vous que les détails des produits sont stockés dans un format approprié dans la session
    
        // Exemple fictif de récupération des détails des produits
        $produitsDetails = [];
        foreach ($commandeInfo['produitIds'] as $produitId) {
            // Récupérer le produit depuis la base de données en fonction de l'identifiant
            $produit = $entityManager->getRepository(TProduits::class)->find($produitId);
            if ($produit) {
                // Ajouter les détails du produit à la liste des détails des produits
                $produitsDetails[] = [
                    'ygoId' => $produit->getYgoId(),
                    'nom' => $produit->getNomProduit(),
                    'prix' => $produit->getPrix(),
                    // Ajoutez d'autres détails du produit si nécessaire
                ];
            }
        }
        
        // Ajoutez les détails des produits à la session 'commande_info'
        $commandeInfo['produits'] = $produitsDetails;
        
        // Passer les informations à la vue
        return $this->render('commandes/new.html.twig', [
            'commandeInfo' => $commandeInfo,
        ]);
    }

    // #[Route('/{id}', name: 'app_commandes_show', methods: ['GET'])]
    // public function show(Commandes $commande): Response
    // {
    //     return $this->render('commandes/show.html.twig', [
    //         'commande' => $commande,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_commandes_show', methods: ['GET'])]
    public function show(Commandes $commande): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN') || $commande->getUser() === $user) {

        $detailCommandes = $commande->getDetailCommande();
        $totalPanier = 0; // Initialiser le total à zéro
        $produitsDetails = [];

        foreach ($detailCommandes as $detailCommande) {
            $produitsDetails[] = [
                'produit' => $detailCommande->getTProduits(),
                'quantite' => $detailCommande->getQuantity(),
                'prix' => $detailCommande->getPrix(),
                'total' => $detailCommande->getQuantity() * $detailCommande->getTProduits()->getPrix()
            ];
            $totalPanier += $detailCommande->getQuantity() * $detailCommande->getTProduits()->getPrix();
        }

            return $this->render('commandes/show.html.twig', [
                'commande' => $commande,
                'produitsDetails' => $produitsDetails,
                'totalPanier' => $totalPanier,
            ]);
        }

        throw $this->createAccessDeniedException('Vous n`\'avez pas l\'autorisation de visualiser cette commande.');

    }

    #[Route('/{id}/edit', name: 'app_commandes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commandes $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandesType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commandes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commandes/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commandes_delete', methods: ['POST'])]
    public function delete(Request $request, Commandes $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commandes_index', [], Response::HTTP_SEE_OTHER);
    }
}
