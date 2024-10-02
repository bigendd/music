<?php

namespace App\Controller;

use App\Service\SpotifyApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    // Route pour la page d'accueil avec le formulaire de recherche
    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        return $this->render('artiste/cherche_form.html.twig');
    }

    // Route pour rechercher des artistes après soumission du formulaire
    #[Route('/artists/search/results', name: 'artist_search_results', methods: ['POST'])]
    public function searchArtists(Request $request): Response
    {
        $query = $request->request->get('artist_name');
        
        // Appel de la méthode du service pour rechercher des artistes par nom
        $artists = $this->spotifyApiService->searchArtists($query);

        // Rendre la vue avec les résultats des artistes trouvés
        return $this->render('artiste/cherche.html.twig', [
            'artists' => $artists['artists']['items'] ?? [],
            'query' => $query,
        ]);
    }

    // Route pour obtenir les détails d'un artiste spécifique par son ID Spotify
    #[Route('/artists/{id}', name: 'artist_info', requirements: ['id' => '.+'])]
    public function artistInfo(string $id): Response
    {
        // Appel de la méthode pour obtenir les informations sur l'artiste via son ID
        $artistData = $this->spotifyApiService->getArtistInfo($id);

        return $this->render('artiste/info.html.twig', [
            'artist' => $artistData,
        ]);
    }
}
