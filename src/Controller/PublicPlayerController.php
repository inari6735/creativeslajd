<?php

namespace App\Controller;

use App\Repository\SlideshowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicPlayerController extends AbstractController
{
    #[Route('/share/{shareToken}', name: 'app_public_player_show', methods: ['GET'])]
    public function show(string $shareToken, SlideshowRepository $repository): Response
    {
        $slideshow = $repository->findOneBy(['shareToken' => $shareToken]);
        
        if (!$slideshow) {
            throw $this->createNotFoundException('Pokaz nie został znaleziony.');
        }
        
        if (!$slideshow->isPublic()) {
            throw $this->createAccessDeniedException('Ten pokaz nie jest publicznie dostępny.');
        }
        
        if ($slideshow->getSlides()->isEmpty()) {
            throw $this->createNotFoundException('Ten pokaz nie zawiera żadnych slajdów.');
        }

        return $this->render('player/show.html.twig', [
            'slideshow' => $slideshow,
            'isPublicView' => true,
        ]);
    }
}
