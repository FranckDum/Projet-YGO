<?php

namespace App\Controller;

use App\Entity\AdresseFacturation;
use App\Entity\User;
use App\Service\CodeManagerService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, CodeManagerService $codeManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setActivation($codeManager->getCode());
            $entityManager->persist($user);
            $adresseFacturation = new AdresseFacturation();
            $adresseFacturation->setUser($user);
            $entityManager->persist($adresseFacturation);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $email = (new TemplatedEmail())
            ->from(new Address('dueldestinyshp@gmail.com', 'DuelDestinyShop'))
            ->to($user->getEmail())
            ->subject('Confirmation du compte')
            ->htmlTemplate('email/inscription.html.twig')
            ->context([
                'user' => $user,
            ]);

            $mailer->send($email);
            $this->addFlash('success', 'Bienvenue, un email vous a été envoyé pour activer votre compte');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/activation/{token}', name: 'activation')]
    public function activation($token, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $user = $userRepository->findOneBy(['activation' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Cet utilisateur n\'existe pas');
        }

        $user->setActivation(NULL);
        $em->flush();
        $this->addFlash('success', 'Votre compte est activé');
        return $this->redirectToRoute('app_login');
    }
}
