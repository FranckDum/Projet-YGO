<?php

namespace App\Controller;

use App\Entity\AdresseFacturation;
use App\Form\AdresseFacturationType;
use App\Repository\AdresseFacturationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil/adresse/facturation')]
class AdresseFacturationController extends AbstractController
{
    #[Route('/', name: 'app_adresse_facturation_index', methods: ['GET'])]
    public function index(AdresseFacturationRepository $adresseFacturationRepository): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $adresseFacturation = $adresseFacturationRepository->findAll();
        } else {
            $adresseFacturation = $adresseFacturationRepository->findBy(['user' => $user]);
        }
        return $this->render('adresse_facturation/index.html.twig', [
            'adresse_facturations' => $adresseFacturation,
        ]);
    }

    #[Route('/new', name: 'app_adresse_facturation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur actuellement authentifié

        $adresseFacturation = new AdresseFacturation();
        $adresseFacturation->setUser($user); // Associer l'ID de l'utilisateur à l'adresse de livraison
        $form = $this->createForm(AdresseFacturationType::class, $adresseFacturation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adresseFacturation);
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_facturation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_facturation/new.html.twig', [
            'adresse_facturation' => $adresseFacturation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_facturation_show', methods: ['GET'])]
    public function show(AdresseFacturation $adresseFacturation): Response
    {
        $user = $this->getUser();
        // Vérifiez si l'utilisateur connecté a le rôle ADMIN
        if ($this->isGranted('ROLE_ADMIN') || $adresseFacturation->getUser() === $user) {
            
            return $this->render('adresse_facturation/show.html.twig', [
                'adresse_facturation' => $adresseFacturation,
            ]);
        }
        throw $this->createAccessDeniedException('Vous devez être un administrateur pour voir la fiche de cette adresse de facturation.');
    }

    #[Route('/{id}/edit', name: 'app_adresse_facturation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdresseFacturation $adresseFacturation, EntityManagerInterface $entityManager): Response
    {
        // Obtenez l'utilisateur connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est bien le propriétaire de l'adresse de livraison
        if ($adresseFacturation->getUser() !== $user) {
            // Si ce n'est pas le cas, lancez une exception ou redirigez vers une autre page
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette adresse de livraison.');
        }

        $form = $this->createForm(AdresseFacturationType::class, $adresseFacturation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_facturation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_facturation/edit.html.twig', [
            'adresse_facturation' => $adresseFacturation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_facturation_delete', methods: ['POST'])]
    public function delete(Request $request, AdresseFacturation $adresseFacturation, EntityManagerInterface $entityManager): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$adresseFacturation->getId(), $request->getPayload()->get('_token'))) {
        //     $entityManager->remove($adresseFacturation);
        //     $entityManager->flush();
        // }

        // return $this->redirectToRoute('app_adresse_facturation_index', [], Response::HTTP_SEE_OTHER);
        if ($this->isCsrfTokenValid('delete'.$adresseFacturation->getId(), $request->request->get('_token'))) {
        $entityManager->remove($adresseFacturation);
        $entityManager->flush();
        }

        return $this->redirectToRoute('app_adresse_facturation_index', [], Response::HTTP_SEE_OTHER);
    }
}
