<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FooterController extends AbstractController
{
    #[Route('/a_propos', name: 'app_a_propos')]
    public function aPropos(): Response
    {
        return $this->render('footer/apropos.html.twig');
    }

    #[Route('/mentions_legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('footer/mentions_legales.html.twig');
    }
}
