<?php

namespace App\Controller;

use App\Entity\Slideshow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/player')]
#[IsGranted('ROLE_USER')]
class PlayerController extends AbstractController
{
    #[Route('/{id}', name: 'app_player_show', methods: ['GET'])]
    public function show(Slideshow $slideshow): Response
    {
        // Sprawdź czy slideshow należy do zalogowanego użytkownika
        if ($slideshow->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tego pokazu.');
        }
        
        if ($slideshow->getSlides()->isEmpty()) {
            $this->addFlash('error', 'Ten pokaz nie zawiera żadnych slajdów');
            return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
        }

        return $this->render('player/show.html.twig', [
            'slideshow' => $slideshow,
        ]);
    }
}
