<?php

namespace App\Controller;

use App\Entity\AdresseLivraison;
use App\Form\AdresseLivraisonType;
use App\Repository\AdresseLivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil/adresse/livraison')]
class AdresseLivraisonController extends AbstractController
{
    #[Route('/', name: 'app_adresse_livraison_index', methods: ['GET'])]
    public function index(AdresseLivraisonRepository $adresseLivraisonRepository): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $adresseLivraison = $adresseLivraisonRepository->findAll();
        } else {
            $adresseLivraison = $adresseLivraisonRepository->findBy(['user' => $user]);
        }
        return $this->render('adresse_livraison/index.html.twig', [
            'adresse_livraisons' => $adresseLivraison,
        ]);
    }

    #[Route('/new', name: 'app_adresse_livraison_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur actuellement authentifié

        $adresseLivraison = new AdresseLivraison();
        $adresseLivraison->setUser($user);; // Associer l'ID de l'utilisateur à l'adresse de livraison
        $form = $this->createForm(AdresseLivraisonType::class, $adresseLivraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adresseLivraison);
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_livraison/new.html.twig', [
            'adresse_livraison' => $adresseLivraison,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_livraison_show', methods: ['GET'])]
    public function show(AdresseLivraison $adresseLivraison): Response
    {
        // Vérifiez si l'utilisateur connecté a le rôle ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous devez être un administrateur pour voir la fiche de cette adresse de livraison.');
        }

        return $this->render('adresse_livraison/show.html.twig', [
            'adresse_livraison' => $adresseLivraison,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_adresse_livraison_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdresseLivraison $adresseLivraison, EntityManagerInterface $entityManager): Response
    {
        // Obtenez l'utilisateur connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est bien le propriétaire de l'adresse de livraison
        if ($adresseLivraison->getUser() !== $user) {
            // Si ce n'est pas le cas, lancez une exception ou redirigez vers une autre page
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette adresse de livraison.');
        }
        
        $form = $this->createForm(AdresseLivraisonType::class, $adresseLivraison);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_livraison/edit.html.twig', [
            'adresse_livraison' => $adresseLivraison,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_livraison_delete', methods: ['POST'])]
    public function delete(Request $request, AdresseLivraison $adresseLivraison, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adresseLivraison->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($adresseLivraison);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_adresse_livraison_index', [], Response::HTTP_SEE_OTHER);
    }
}
