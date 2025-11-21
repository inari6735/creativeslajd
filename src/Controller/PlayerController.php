<?php

namespace App\Controller;

use App\Entity\Slideshow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/player')]
class PlayerController extends AbstractController
{
    #[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    public function show(Slideshow $slideshow): Response
    {
        if ($slideshow->getSlides()->isEmpty()) {
            $this->addFlash('error', 'Ten pokaz nie zawiera żadnych slajdów');
            return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
        }

        return $this->render('player/show.html.twig', [
            'slideshow' => $slideshow,
        ]);
    }
}
