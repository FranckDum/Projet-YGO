<?php

namespace App\Controller;

use Stripe\Charge;
use Stripe\Stripe;
use App\Entity\Commandes;
use App\Entity\DetailCommande;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    private $tProduitsRepository;
    private $session;

    public function __construct(TProduitsRepository $tProduitsRepository, RequestStack $requestStack) {
        $this->tProduitsRepository = $tProduitsRepository;
        $this->session = $requestStack->getSession();
    }

    /** 
     *  getMontantTotal permet de calculer le montant total du panier en centimes
     *  
     *  @return float
    */

    
    public function getMontantTotal(): float
    {
        $montant = 0;
        $panier = $this->session->get('panier', []);
        foreach($panier as $idProduit => $quantite){
            $produit = $this->tProduitsRepository->find($idProduit);
            $montant += ($produit->getPrix() * 100) * $quantite;
        }

        return $montant;
    }
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        /*
            CB SUCCESS : 4242 4242 4242 4242
            CB FAILURE : 4000 0000 0000 0002
        */

        $montant = $this->getMontantTotal(); // montant en centimes

        $stripePublicKey = $_ENV['STRIPE_PUBLIC_KEY'];

        return $this->render('stripe/index.html.twig', [
            'stripe_public_key' => $stripePublicKey,
            'montant' => $montant
        ]);
    }

    #[Route('/process_payment', name: 'process_payment')]
    public function processPayment(Request $request)
    {
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
        Stripe::setApiKey($stripeSecretKey);
        $token = $request->request->get('stripeToken');

        // paiement success
        try {

            $charge = Charge::create([
                'amount' => $this->getMontantTotal(),
                'currency' => 'eur',
                'description' => 'Achat effectué sur DuelDestinyShop!',
                'source' => $token
                
            ]);

            $stripeChargeId = $charge->id; // id du stripe de la commande

            return $this->redirectToRoute('payment_success');
        } catch (\Exception $e) {
            // paiement failure
            return $this->redirectToRoute('payment_failure');
        }
    }

    #[Route('/payment_success', name: 'payment_success')]
    public function paymentSuccess(EntityManagerInterface $em): Response
    {
    // Récupérer les informations de la session ou d'autres sources appropriées
    $statut = $this->session->get('statut', 'en cours');
    $numeros = $this->session->get('numeros', null);
    $user = $this->getUser(); // Cela suppose que vous utilisez Symfony pour gérer l'authentification des utilisateurs
    $prix = $this->getMontantTotal(); // Ou toute autre source appropriée pour le prix

            // Créer une nouvelle commande
    $commande = new Commandes();
    $commande->setDateCommande(new \DateTimeImmutable());
    $commande->setStatut($statut);
    $commande->setNumeros($numeros);
    $commande->setUser($user);
    // Vous pouvez ajouter d'autres données à votre commande ici

    $panier = $this->session->get('panier', []);

    foreach($panier as $idProduit => $quantite) {
        $produit = $this->tProduitsRepository->find($idProduit);

        // Soustraire la quantité du produit du stock
        $nouveauStock = $produit->getStock() - $quantite;
        $produit->setStock($nouveauStock);

        // Créer un nouvel élément de détail de commande
        $detailCommande = new DetailCommande();
        $detailCommande->setCommandes($commande);
        $detailCommande->setTProduits($produit);
        $detailCommande->setPrix($prix);
        $detailCommande->setQuantity($quantite);
        // Vous pouvez ajouter d'autres données au détail de la commande ici

        $em->persist($detailCommande);
    }

    // Vider le panier
    $this->session->set('panier', []);

    // Mettre à jour les stocks dans la base de données
    $em->flush();

    return $this->render('stripe/payment_success.html.twig');
    }

    #[Route('/payment_failure', name: 'payment_failure')]
    public function paymentFailure(): Response
    {
        return $this->render('stripe/payment_failure.html.twig');
    }
}

