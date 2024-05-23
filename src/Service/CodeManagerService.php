<?php

namespace App\Service;

class CodeManagerService
{

    /**
     * Génère un token aléatoire d'un nombre de caractères
     *
     * @return string
     */
    public function getCode(): string
    {
        $caractere = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        shuffle($caractere);
        $count = count($caractere);
        $chaine = '';
        for ($i = 1; $i <= 10; $i++) {
            $rand = rand(0, $count - 1);
            $chaine .= $caractere[$rand];
        }
        return $chaine;
    }

}
