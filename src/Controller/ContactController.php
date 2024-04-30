<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Services\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerService $mailer)
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $contactFormData = $form->getData();
            $subject = 'Demande de contact sur votre site de ' . $contactFormData['email'];
            $content = $contactFormData['name'] . ' vous a envoyé le message suivant: ' . $contactFormData['message'];
            $mailer->sendEmail(subject: $subject, content: $content);
            $this->addFlash('success', 'Votre message a été envoyé');
            return $this->redirectToRoute('accueil');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}



// namespace App\Controller;

// use Symfony\Component\Mime\Email;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Mailer\MailerInterface;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Validator\Constraints as Assert;
// use Symfony\Component\Validator\Validator\ValidatorInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


// class ContactController extends AbstractController
// {
//     public function sendContactForm(Request $request, MailerInterface $mailer, ValidatorInterface $validator): Response
//     {
//         // Récupérer les données du formulaire
//         $name = $request->request->get('contactName');
//         $email = $request->request->get('contactEmail');
//         $message = $request->request->get('contactMessage');
//         dd($name, $email, $message);

//         // Validation côté serveur
//         if (empty($name) || empty($email) || empty($message)) {
//             // Gérer les erreurs de validation
//             // Afficher un message d'erreur
//             $this->addFlash('error', 'Veuillez remplir tous les champs du formulaire.');
//         }

//         // Validation côté serveur
//         $errors = $validator->validate($email, new Assert\Email());

//         if (count($errors) > 0) {
//             // Gérer les erreurs de validation
//             // Afficher un message d'erreur
//             $this->addFlash('error', 'Veuillez fournir une adresse email valide.');
//         }

//         // Envoyer l'e-mail
//         $email = (new Email())
//             ->from($email)
//             ->to('dueldestinyshp@gmail.com')
//             ->subject('Nouveau message de contact')
//             ->html('<p>Nom: ' . $name . '</p><p>Email: ' . $email . '</p><p>Message: ' . $message . '</p>');

//         $mailer->send($email);

//         // Message de confirmation
//         $this->addFlash('success', 'Votre message a été envoyé avec succès.');

//         // Rediriger l'utilisateur
//         return $this->redirectToRoute('accueil');
//     }
// }
