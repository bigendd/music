<?php

namespace App\Controller;

use App\Service\SpotifyApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtisteController extends AbstractController
{
    private $spotifyApiService;

    public function __construct(SpotifyApiService $spotifyApiService)
    {
        $this->spotifyApiService = $spotifyApiService;
    }

    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        // Récupérer les nouvelles sorties
        $newReleases = $this->spotifyApiService->getNewReleases();
    
        return $this->render('artiste/cherche_form.html.twig', [
            'newReleases' => $newReleases['albums']['items'] ?? [],
        ]);
    }
    


    #[Route('/artists/search/results', name: 'artist_search_results', methods: ['POST'])]
    public function searchArtists(Request $request): Response
    {
        $query = $request->request->get('artist_name');
        $artists = $this->spotifyApiService->searchArtists($query);

        return $this->render('artiste/cherche.html.twig', [
            'artists' => $artists['artists']['items'] ?? [],
            'query' => $query,
        ]);
    }

    #[Route('/artists/{id}', name: 'artist_info', requirements: ['id' => '.+'])]
    public function artistInfo(string $id): Response
    {
        $artistData = $this->spotifyApiService->getArtistInfo($id);

        return $this->render('artiste/info.html.twig', [
            'artist' => $artistData,
        ]);
    }

    #[Route('/autocomplete', name: 'artist_autocomplete')]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query->get('query');
        
        if (!$query || strlen($query) < 2) {
            return new JsonResponse([]);
        }

        $artists = $this->spotifyApiService->searchArtists($query);

        // Formater les résultats pour l'autocomplétion
        $suggestions = array_map(function ($artist) {
            return [
                'id' => $artist['id'],
                'name' => $artist['name'],
            ];
        }, $artists['artists']['items'] ?? []);

        return new JsonResponse($suggestions);
    }
}
