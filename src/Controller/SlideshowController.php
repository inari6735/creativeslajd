<?php

namespace App\Controller;

use App\Entity\Slideshow;
use App\Form\SlideshowType;
use App\Repository\SlideshowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/slideshow')]
class SlideshowController extends AbstractController
{
    #[Route('/', name: 'app_slideshow_index', methods: ['GET'])]
    public function index(SlideshowRepository $repository): Response
    {
        return $this->render('slideshow/index.html.twig', [
            'slideshows' => $repository->findAllOrderedByDate(),
        ]);
    }

    #[Route('/new', name: 'app_slideshow_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $slideshow = new Slideshow();
        $form = $this->createForm(SlideshowType::class, $slideshow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($this->isCsrfTokenValid('delete'.$slideshow->getId(), $request->request->get('_token'))) {
            $entityManager->remove($slideshow);
            $entityManager->flush();

            $this->addFlash('success', 'Pokaz slajdów został usunięty!');
        }

        return $this->redirectToRoute('app_slideshow_index');
    }
}
