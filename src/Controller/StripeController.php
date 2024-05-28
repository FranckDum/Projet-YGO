<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Stripe\Charge;
use Stripe\Stripe;
use Dompdf\Options;
use App\Entity\Commandes;
use App\Entity\Livraison;
use App\Entity\DetailCommande;
use App\Entity\AdresseLivraison;
use Symfony\Component\Mime\Email;
use App\Entity\AdresseFacturation;
use Symfony\Component\Mime\Address;
use App\Repository\TProduitsRepository;
use App\Entity\AdresseLivraisonCommande;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AdresseFacturationCommande;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\NumerosCommandesGeneratorService;
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
        return $this->session->get('montantTotalCommande', 0) * 100;
    }


    #[Route('/stripe', name: 'app_stripe')]    
    /**
     * Method index
     *
     * @return Response
     */
    
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
public function processPayment(Request $request, SessionInterface $session)
{


    // Configurer l'API Stripe
    $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
    Stripe::setApiKey($stripeSecretKey);
    $token = $request->request->get('stripeToken');

    // Calculer le montant total de la commande
    $montant = $this->getMontantTotal(); // Cette méthode doit calculer le montant total

    if ($montant < 1) {
        $this->addFlash('error', 'Montant invalide: ' . $montant);
        return $this->redirectToRoute('payment_failure');
    }

    try {
        $charge = Charge::create([
            'amount' => $montant, // Le montant doit être en centimes
            'currency' => 'eur',
            'description' => 'Achat effectué sur DuelDestinyShop!',
            'source' => $token
        ]);

        $stripeId = $charge->id; // ID Stripe de la commande

        // Stocker l'ID Stripe dans la session
        $session->set('stripeId', $stripeId);

        return $this->redirectToRoute('payment_success');
    } catch (\Exception $e) {
        // Log the error message
        $this->addFlash('error', 'Payment failed: ' . $e->getMessage());
        error_log('Payment failed: ' . $e->getMessage());

        return $this->redirectToRoute('payment_failure');
    }
}

    #[Route('/payment_success', name: 'payment_success')]    
    /**
     * Method paymentSuccess
     *
     * @param EntityManagerInterface $em [explicite description]
     * @param NumerosCommandesGeneratorService $num [explicite description]
     * @param MailerInterface $mailer [explicite description]
     *
     * @return Response
     */
    
    public function paymentSuccess(EntityManagerInterface $em, NumerosCommandesGeneratorService $num, MailerInterface $mailer): Response
    {
    // Récupérer les informations de la session ou d'autres sources appropriées
    $statut = $this->session->get('statut', 'en cours');
    // $numeros = $this->session->get('numeros', null);
    $user = $this->getUser(); // Gérer l'authentification des utilisateurs
    $prix = $this->getMontantTotal(); // Montant total

    // Créer une nouvelle commande
    $commande = new Commandes();

    $commande->setDateCommande(new \DateTimeImmutable());
    $commande->setStatut($statut);
    $commande->setNumeros($num->generateNumeros());
    $commande->setUser($user);
    $commande->setMontantTotal($prix);
    // Récupérer l'ID de l'adresse de livraison depuis la session
    $adresseLivraisonId = $this->session->get('adresse_livraison_id');

    dump($adresseLivraisonId); // Vérifiez l'ID récupéré depuis la session

    // Récupérer l'adresse de livraison depuis la base de données
    $adresseLivraison = $em->getRepository(AdresseLivraison::class)->find($adresseLivraisonId);

    dump($adresseLivraison); // Vérifiez l'objet adresseLivraison récupéré depuis la base de données

    if ($adresseLivraison) {
        // Utilisez l'adresse de livraison pour remplir les détails de l'adresse de livraison de la commande
        $livraisonCommande = new AdresseLivraisonCommande();
        $livraisonCommande->setNom($adresseLivraison->getNom());
        $livraisonCommande->setPrenom($adresseLivraison->getPrenom());
        $livraisonCommande->setAdresse($adresseLivraison->getAdresse());
        $livraisonCommande->setComplementAdresse($adresseLivraison->getComplementAdresse());
        $livraisonCommande->setCp($adresseLivraison->getCp());
        $livraisonCommande->setVille($adresseLivraison->getVille());

        // Associez cette adresse de livraison à la commande
        $commande->setAdresseLivraisonCommande($livraisonCommande);
    } else {
        // Gérez l'erreur si l'adresse de livraison n'est pas trouvée
        // Par exemple, redirigez l'utilisateur vers une page d'erreur
        return $this->redirectToRoute('error_page');
    }

    // $commande->setAdresseLivraisonCommande($livraisonCommande);
    // Récupérer l'ID de l'adresse de facturation depuis la session
    $adresseFacturationId = $this->session->get('adresse_facturation_id');

    // Récupérer l'adresse de facturation depuis la base de données en utilisant son ID
    $adresseFacturation = $em->getRepository(AdresseFacturation::class)->find($adresseFacturationId);

    dump($adresseFacturation);
    // Vérifier si l'adresse de facturation a été trouvée
    if ($adresseFacturation) {
        // Créez un nouvel objet AdresseFacturationCommande et remplissez-le avec les données de l'adresse de facturation
        $facturationCommande = new AdresseFacturationCommande();
        $facturationCommande->setNom($adresseFacturation->getNom());
        $facturationCommande->setPrenom($adresseFacturation->getPrenom());
        $facturationCommande->setAdresse($adresseFacturation->getAdresse());
        $facturationCommande->setComplementAdresse($adresseFacturation->getComplementAdresse());
        $facturationCommande->setCp($adresseFacturation->getCp());
        $facturationCommande->setVille($adresseFacturation->getVille());

        // Associez cette adresse de facturation à la commande
        $commande->setAdresseFacturationCommande($facturationCommande);
    } else {
        // Gérez l'erreur si l'adresse de facturation n'est pas trouvée
        return $this->redirectToRoute('error_page');
    }
    // $commande->setAdresseFacturationCommande($facturationCommande);
            // Récupérer l'ID du mode de livraison depuis la session
            $livraisonId = $this->session->get('mode_livraison');
            dump($livraisonId);
            if (!$livraisonId) {
                // Gérez l'erreur si l'ID du mode de livraison n'est pas trouvé dans la session
                return $this->redirectToRoute('app_validation_panier');
            }
            
            // Récupérer le mode de livraison depuis la base de données en utilisant son ID
            $livraison = $em->getRepository(Livraison::class)->find($livraisonId);

            if ($livraison) {
                // Associer l'objet Livraison récupéré à la commande
                $commande->setLivraison($livraison);
            } else {
                // Gérez l'erreur si le mode de livraison n'est pas trouvé
                return $this->redirectToRoute('app_validation_panier');
            }

    // Récupérer l'ID Stripe de la session
    $stripeId = $this->session->get('stripeId');
    $commande->setStripeId($stripeId); // Associer l'ID Stripe à la commande

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
        $detailCommande->setPrix($produit->getPrix());
        $detailCommande->setQuantity($quantite);

        // Ajoutez le détail de la commande à la collection de la commande
        $commande->addDetailCommande($detailCommande);

        $em->persist($detailCommande);
    }

    $em->persist($commande);

    // Vider le panier
    $this->session->set('panier', []);
    $this->session->set('adresse_livraison_id', null);
    $this->session->set('mode_livraison', null);

    // Mettre à jour les stocks dans la base de données
    $em->flush();

        // Récupérer les détails de la commande
        $detailsCommande = $commande->getDetailCommande();

    // Configurer les options de Dompdf
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    $pdfOptions->set('isHtml5ParserEnabled', true);// Activer le parser HTML5
    $pdfOptions->set('isPhpEnabled', true);  // Activer le support PHP

    // Initialiser Dompdf avec les options
    $dompdf = new Dompdf($pdfOptions); 
    // $dompdf->set_option("enable_php", true);

    $html = $this->renderView('pdf/fiche_commande.html.twig', [
        'commande' => $commande,
        'detailsCommande' => $detailsCommande,
    ]);

    // Charger le HTML dans Dompdf
    $dompdf->loadHtml($html);
    // (Facultatif) Configurer le format et l'orientation du papier 'portrait' ou 'portrait'
    $dompdf->setPaper('A4', 'portrait');
    // Rendre le HTML au format PDF
    $dompdf->render();

    // Sortie du PDF généré dans le navigateur (téléchargement forcé)
    $pdf = $dompdf->output();

    $fichier = $commande->getNumeros() . '.pdf';

    $location = $this->getParameter('private') . '/' . $fichier;
    file_put_contents($location, $pdf);

    // mail ->attachFromPath($location);
    // Envoi de l'email avec le PDF en pièce jointe
    $email = (new Email())
        ->from(new Address('duelDestinyShp@gmail.com', 'DuelDestinyShop!'))
        ->to($user->getEmail())
        ->subject('Merci pour votre commande')
        ->html('<p>Merci pour votre commande.</p>')
        ->attachFromPath($location);

    $mailer->send($email);

    return $this->render('stripe/payment_success.html.twig', [
        'commande' => $commande,
        'livraison' => $livraison,
        'adresseFacturation' => $adresseFacturation,
        'adresseLivraison' => $adresseLivraison,
        'detailsCommande' => $detailsCommande,
        'montantTotal' => $prix,
    ]);
    }

    #[Route('/payment_failure', name: 'payment_failure')]    
    /**
     * Method paymentFailure
     *
     * @return Response
     */
    public function paymentFailure(): Response
    {
        $this->session->set('mode_livraison', null);
        return $this->render('stripe/payment_failure.html.twig');
    }
}

