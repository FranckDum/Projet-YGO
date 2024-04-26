<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('', name: 'app_panier')]
    public function index(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        return $this->render('panier/index.html.twig', [
            'controller_name' => 'PanierController',
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
}
