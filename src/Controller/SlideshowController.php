<?php

namespace App\Controller;

use App\Entity\Slideshow;
use App\Form\SlideshowType;
use App\Repository\SlideshowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[Route('/slideshow')]
#[IsGranted('ROLE_USER')]
class SlideshowController extends AbstractController
{
    #[Route('/', name: 'app_slideshow_index', methods: ['GET'])]
    public function index(SlideshowRepository $repository): Response
    {
        $user = $this->getUser();
        
        return $this->render('slideshow/index.html.twig', [
            'slideshows' => $repository->findBy(['user' => $user], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_slideshow_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $slideshow = new Slideshow();
        $form = $this->createForm(SlideshowType::class, $slideshow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slideshow->setUser($this->getUser());
            $entityManager->persist($slideshow);
            $entityManager->flush();

            $this->addFlash('success', 'Pokaz slajdów został utworzony!');

            return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
        }

        return $this->render('slideshow/form.html.twig', [
            'slideshow' => $slideshow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_slideshow_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Slideshow $slideshow, EntityManagerInterface $entityManager): Response
    {
        // Sprawdź czy slideshow należy do zalogowanego użytkownika
        if ($slideshow->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tego pokazu.');
        }
        
        $form = $this->createForm(SlideshowType::class, $slideshow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slideshow->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Pokaz slajdów został zaktualizowany!');

            return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
        }

        return $this->render('slideshow/edit.html.twig', [
            'slideshow' => $slideshow,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_slideshow_delete', methods: ['POST'])]
    public function delete(Request $request, Slideshow $slideshow, EntityManagerInterface $entityManager): Response
    {
        // Sprawdź czy slideshow należy do zalogowanego użytkownika
        if ($slideshow->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Nie masz dostępu do tego pokazu.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$slideshow->getId(), $request->request->get('_token'))) {
            $entityManager->remove($slideshow);
            $entityManager->flush();

            $this->addFlash('success', 'Pokaz slajdów został usunięty!');
        }

        return $this->redirectToRoute('app_slideshow_index');
    }

    #[Route('/{id}/youtube-control', name: 'app_slideshow_youtube_control', methods: ['POST'])]
    public function youtubeControl(
        Request $request,
        Slideshow $slideshow,
        HubInterface $hub
    ): JsonResponse {
        // Check access (either owner or public editable)
        if ($slideshow->getUser() !== $this->getUser() && !$slideshow->isPubliclyEditable()) {
            return new JsonResponse(['success' => false, 'error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['action'])) {
            return new JsonResponse(['success' => false, 'error' => 'Missing action'], 400);
        }

        $action = $data['action'];
        $videoId = $data['videoId'] ?? null;

        // Publish to Mercure
        $topic = sprintf('slideshow/%d/youtube', $slideshow->getId());
        
        $updateData = [
            'action' => $action,
            'videoId' => $videoId,
            'slideshowId' => $slideshow->getId(),
            'timestamp' => time(),
        ];

        $update = new Update(
            $topic,
            json_encode($updateData),
            false
        );

        try {
            $hub->publish($update);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
