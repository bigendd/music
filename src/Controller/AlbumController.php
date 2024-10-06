<?php

namespace App\Controller;

use App\Service\SpotifyApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    private $spotifyApiService;

    public function __construct(SpotifyApiService $spotifyApiService)
    {
        $this->spotifyApiService = $spotifyApiService;
    }

    #[Route('/albums/{id}', name: 'album_info', requirements: ['id' => '.+'])]
    public function albumInfo(string $id): Response
    {
        $albumData = $this->spotifyApiService->getAlbumInfo($id); 
    
        
    
        return $this->render('album/index.html.twig', [
            'album' => $albumData,
        ]);
    }
    
}
