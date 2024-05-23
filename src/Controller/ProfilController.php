<?php

namespace App\Controller;

use App\Form\EditUserProfilFormType;
use App\Form\EditUserPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profil', methods:['GET', 'POST'])]
class ProfilController extends AbstractController
{
    #[Route('/espace', name: 'app_profil_espace', methods:['GET'])]
    public function espace(): Response
    {
        return $this->render('profil/espace.html.twig', []);
    }

    #[Route('/', name: 'app_profil', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', []);
    }

    #[Route('/edit', name: 'app_profil_edit', methods:['GET', 'PUT'])]
    public function editProfil(Request $request, EntityManagerInterface $em): Response
    {
        
        $user = $this->getUser();
        $form = $this->createForm(EditUserProfilFormType::class, $user, [
            "method" => "PUT",
            "user"   => $user
        ]);
        
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "Le profil a été modifié");

            return $this->redirectToRoute("app_profil");
        }

        return $this->render('profil/edit_profil.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/password', name: 'app_profil_edit_password', methods:['GET', 'PUT'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {

        /**
         * @var Ap\Entity\User
         */
        $user = $this->getUser();

        $form = $this->createForm(EditUserPasswordFormType::class, null, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $plainPassword = $form->getData()['plainPassword'];

            $passwordHashed = $hasher->hashPassword($user, $plainPassword);

            $user->setPassword($passwordHashed);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Le mot de passe a été modifié');

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit_password_profil.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete',name: 'app_profil_delete', methods:['DELETE'])]
    public function deleteProfil(Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_profil', $request->request->get('csrf_token')))
        {
            /** @var App\Entity\User */
            $user = $this->getUser();
            $this->addFlash('success', "{$user->getNom()} {$user->getPrenom()} a été supprimé!" );

            $this->container->get('security.token_storage')->setToken(null);

            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_profil');
    }
}
