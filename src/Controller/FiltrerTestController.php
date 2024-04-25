<?php

namespace App\Controller;

use App\Form\FilterCardsFormType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FiltrerTestController extends AbstractController
{
    #[Route('/all-cards', name: 'app_all_cards', methods: ['GET'])]
    public function index(
        HttpClientInterface $client, 
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        // 1- Récupérer toutes les cartes
        // $client
        $responseApi = $client->request('GET', "https://db.ygoprodeck.com/api/v7/cardinfo.php");
        $responseApiArray = $responseApi->toArray();

        $filterTypes        = [];
        $filterAtks         = [];
        $filterDefs         = [];
        $filterLevels       = [];
        $filterRaces        = [];
        $filterAttributes   = [];
        // 2- Extraire 6 infos en fonction de chaque carte: type, atk, def, level, race, attribute
        //  Les ranger chacune dans son, tableau


        // dd($responseApiArray['data']);

        foreach ($responseApiArray['data'] as $card) 
        {
            if (isset($card['type']) && !empty($card['type'])) 
            {
                $filterTypes[] = $card['type'];
            }

            if (isset($card['atk']) && !empty($card['atk'])) 
            {
                $filterAtks[] = $card['atk'];
            }

            if (isset($card['def']) && !empty($card['def'])) 
            {
                $filterDefs[] = $card['def'];
            }

            if (isset($card['level']) && !empty($card['level'])) 
            {
                $filterLevels[] = $card['level'];
            }

            if (isset($card['race']) && !empty($card['race'])) 
            {
                $filterRaces[] = $card['race'];
            }

            if (isset($card['attribute']) && !empty($card['attribute'])) 
            {
                $filterAttributes[] = $card['attribute'];
            }
        }

        // dd(\array_unique($filterTypes));

        // 3- Passer chaque tableau au type du formulaire
        //  Afin de recuperer les données et les insérer dans les champs
        // Créer le formulaire
        $form = $this->createForm(FilterCardsFormType::class, null, [
            "filterTypes"       => \array_unique($filterTypes),
            "filterAtks"        => \array_unique($filterAtks),
            "filterDefs"        => \array_unique($filterDefs),
            "filterLevels"      => \array_unique($filterLevels),
            "filterRaces"       => \array_unique($filterRaces),
            "filterAttributes"  => \array_unique($filterAttributes),
        ]);


        // 4- Afficher le formulaire du filtre sur la page

        // Recupérer les données de la requête
        // Associer au formulaire les données de la requête

        $form->handleRequest($request);

        // Si le formulaire est soumis et que le formulaire est valide,
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Récupérer chaque filtre sélectionné
            $formData = $form->getData();

            $url = "https://db.ygoprodeck.com/api/v7/cardinfo.php?";

            $prepareFilters = ["type", "def", "level", "race", "attribute"];

            foreach ($formData as $key => $value) 
            {
                if (in_array($key, $prepareFilters) && !empty($value)) 
                {
                    $url = "{$url}{$key}={$value}&"; 
                }
            }

            mb_substr($url, -1, 1);
            $url = mb_substr($url, 0, -1);

            // dd($url);

            $filterResponseApi = $client->request("GET", $url);

            // dd($filterResponseApi);
            
            // dd('test');
            // dd($filterResponseApi->getStatusCode());

            if ( 400 == $filterResponseApi->getStatusCode() ) 
            {
                $this->addFlash("warning", "Aucun résultat trouvé.");
                return $this->redirectToRoute("app_all_cards");
            }

            $filterResponseApiArray = $filterResponseApi->toArray();

            $cards = $paginator->paginate(
                $filterResponseApiArray['data'], /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );
    
            return $this->render('filtrer_test/index.html.twig', [
                "form" => $form->createView(),
                "cards" => $cards
            ]);


        }

        // Préparer l'url en fonction du filtre choisi

        // Effectuer la requête à l'api afin de récupérer uniquement les cartes en fonction des filtres

        $cards = $paginator->paginate(
            $responseApiArray['data'], /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('filtrer_test/index.html.twig', [
            "form" => $form->createView(),
            "cards" => $cards
        ]);
    }
}
