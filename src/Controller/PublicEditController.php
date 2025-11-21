<?php

namespace App\Controller;

use App\Entity\Slide;
use App\Form\SlideshowType;
use App\Repository\SlideshowRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/edit')]
class PublicEditController extends AbstractController
{
    #[Route('/{editToken}', name: 'app_public_edit_show', methods: ['GET', 'POST'])]
    public function edit(
        string $editToken,
        Request $request,
        SlideshowRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $slideshow = $repository->findOneBy(['editToken' => $editToken]);
        
        if (!$slideshow) {
            throw $this->createNotFoundException('Pokaz nie został znaleziony.');
        }
        
        if (!$slideshow->isPubliclyEditable()) {
            throw $this->createAccessDeniedException('Ten pokaz nie jest publicznie edytowalny.');
        }

        $form = $this->createForm(SlideshowType::class, $slideshow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slideshow->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Pokaz został zaktualizowany!');

            return $this->redirectToRoute('app_public_edit_show', ['editToken' => $editToken]);
        }

        return $this->render('public_edit/edit.html.twig', [
            'slideshow' => $slideshow,
            'form' => $form,
            'editToken' => $editToken,
        ]);
    }

    #[Route('/{editToken}/upload', name: 'app_public_edit_upload', methods: ['POST'])]
    public function upload(
        string $editToken,
        Request $request,
        SlideshowRepository $repository,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager
    ): Response {
        $slideshow = $repository->findOneBy(['editToken' => $editToken]);
        
        if (!$slideshow || !$slideshow->isPubliclyEditable()) {
            throw $this->createAccessDeniedException();
        }

        $files = $request->files->get('images');
        
        if (!$files || (is_array($files) && count($files) === 0)) {
            $this->addFlash('error', 'Nie wybrano żadnych plików');
            return $this->redirectToRoute('app_public_edit_show', ['editToken' => $editToken]);
        }

        $uploadedCount = 0;
        $maxPosition = 0;
        
        foreach ($slideshow->getSlides() as $existingSlide) {
            if ($existingSlide->getPosition() > $maxPosition) {
                $maxPosition = $existingSlide->getPosition();
            }
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $fileName = $fileUploader->upload($file);
                    
                    $slide = new Slide();
                    $slide->setImagePath($fileName);
                    $slide->setPosition(++$maxPosition);
                    $slide->setSlideshow($slideshow);
                    
                    $entityManager->persist($slide);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Błąd przy przesyłaniu pliku: ' . $e->getMessage());
                }
            }
        }

        if ($uploadedCount > 0) {
            $entityManager->flush();
            $this->addFlash('success', "Dodano $uploadedCount " . ($uploadedCount === 1 ? 'slajd' : 'slajdów'));
        }

        return $this->redirectToRoute('app_public_edit_show', ['editToken' => $editToken]);
    }

    #[Route('/{editToken}/slide/{slideId}', name: 'app_public_edit_delete_slide', methods: ['POST'])]
    public function deleteSlide(
        string $editToken,
        int $slideId,
        Request $request,
        SlideshowRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $slideshow = $repository->findOneBy(['editToken' => $editToken]);
        
        if (!$slideshow || !$slideshow->isPubliclyEditable()) {
            throw $this->createAccessDeniedException();
        }

        $slide = $entityManager->getRepository(Slide::class)->find($slideId);
        
        if (!$slide || $slide->getSlideshow() !== $slideshow) {
            throw $this->createNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete'.$slide->getId(), $request->request->get('_token'))) {
            // Delete physical file
            $filePath = $this->getParameter('slides_directory') . '/' . $slide->getImagePath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $entityManager->remove($slide);
            $entityManager->flush();

            $this->addFlash('success', 'Slajd został usunięty!');
        }

        return $this->redirectToRoute('app_public_edit_show', ['editToken' => $editToken]);
    }

    #[Route('/{editToken}/reorder', name: 'app_public_edit_reorder', methods: ['POST'])]
    public function reorder(
        string $editToken,
        Request $request,
        SlideshowRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $slideshow = $repository->findOneBy(['editToken' => $editToken]);
        
        if (!$slideshow || !$slideshow->isPubliclyEditable()) {
            return $this->json(['success' => false, 'message' => 'Brak dostępu'], 403);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['order'])) {
            return $this->json(['success' => false, 'message' => 'Brak danych'], 400);
        }

        foreach ($data['order'] as $position => $slideId) {
            $slide = $entityManager->getRepository(Slide::class)->find($slideId);
            if ($slide && $slide->getSlideshow() === $slideshow) {
                $slide->setPosition($position);
            }
        }

        $entityManager->flush();

        return $this->json(['success' => true]);
    }
}
