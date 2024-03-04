<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Form\TProduitsType;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/t/produits')]
class TProduitsController extends AbstractController
{
    #[Route('/', name: 'app_t_produits_index', methods: ['GET'])]
    public function index(TProduitsRepository $tProduitsRepository): Response
    {
        return $this->render('t_produits/index.html.twig', [
            't_produits' => $tProduitsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_t_produits_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tProduit = new TProduits();
        $form = $this->createForm(TProduitsType::class, $tProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_t_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('t_produits/new.html.twig', [
            't_produit' => $tProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_t_produits_show', methods: ['GET'])]
    public function show(TProduits $tProduit): Response
    {
        return $this->render('t_produits/show.html.twig', [
            't_produit' => $tProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_t_produits_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TProduits $tProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TProduitsType::class, $tProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_t_produits_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('t_produits/edit.html.twig', [
            't_produit' => $tProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_t_produits_delete', methods: ['POST'])]
    public function delete(Request $request, TProduits $tProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tProduit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_t_produits_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/toggle-activation', name: 'toggle_activation', methods: ['POST'])]
    public function toggleActivation($id, Request $request, EntityManagerInterface $em, TProduitsRepository $tProduitsRepository): JsonResponse
    {
        $productId = $em->getRepository(TProduits::class)->find($id);;

        // Récupérer le produit depuis le repository
        $product = $tProduitsRepository->find($productId);

        // Vérifier si le produit existe
        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Inverser l'état d'activation
        $product->setActivation(!$product->isActivation());

        // Enregistrer les modifications dans la base de données
        $em->flush();

        // Répondre avec succès
        return new JsonResponse(['success' => true, 'message' => 'État d\'activation mis à jour avec succès.']);
    }


}
