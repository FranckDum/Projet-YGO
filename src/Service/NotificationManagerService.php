<?php

namespace App\Service;

use App\Service\PanierService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NotificationManagerService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack) {

        $this->requestStack = $requestStack;
    }

    public function getQuantitePanier(): int
    {
        $quantite = 0;

        $session = $this->requestStack->getSession();

        $panier = $session->get('panier', []);

        if($panier) {
            foreach ($panier as $value) {
                $quantite += $value;
            }
        }

        return $quantite;
    }

}