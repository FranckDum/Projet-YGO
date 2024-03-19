<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Form\TProduitsType;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/t/produits')]
class TProduitsController extends AbstractController
{
    #[Route('/', name: 'app_t_produits_index', methods: ['GET', 'POST'])]
    public function index(
        TProduitsRepository $tProduitsRepository, 
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $search = $request->query->get('search');
        $data = $tProduitsRepository->findFilter($search);

        $tProduits = $paginator->paginate(
            $data, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        
        return $this->render('t_produits/index.html.twig', [
            't_produits' => $tProduits,
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

    #[Route('/toggle-activation/{id<\d+>}', name: 'toggle_activation', methods: ['POST'])]
    public function toggleActivation(TProduits $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Activer ou désactiver le produit
        if ($product->isActivation() === false) {
            $product->setActivation(true);
        } else {
            $product->setActivation(false);
        }

        // Enregistrer les modifications dans la base de données
        $entityManager->persist($product);
        $entityManager->flush();

        // Récupérer le paramètre de recherche de la requête actuelle
        $search = $request->query->get('search');

        // Rediriger vers la route index en incluant uniquement le paramètre de recherche
        return $this->redirectToRoute("app_t_produits_index", ['search' => $search]);
    }


}
