<?php

namespace App\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class TwigRuntime implements RuntimeExtensionInterface
{
    private $session;
    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function searchProduitPanier($produitId): array
    {
        $panier = $this->session->get('panier', []);
        
        if (array_key_exists($produitId, $panier)) {
            return [
                'search' => true,
                'q' => $panier[$produitId]
            ];
        } else {
            return [
                'search' => false,
            ];
        }
    }
}
