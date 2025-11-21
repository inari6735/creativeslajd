<?php

namespace App\Controller;

use App\Entity\Slide;
use App\Entity\Slideshow;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/slide')]
class SlideController extends AbstractController
{
    #[Route('/upload/{id}', name: 'app_slide_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        Slideshow $slideshow,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager
    ): Response {
        $files = $request->files->get('images');
        
        // Debug: sprawdź co przychodzi
        if (!$files || (is_array($files) && count($files) === 0)) {
            $this->addFlash('error', 'Nie wybrano żadnych plików');
            return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
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

        return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshow->getId()]);
    }

    #[Route('/{id}', name: 'app_slide_delete', methods: ['POST'])]
    public function delete(Request $request, Slide $slide, EntityManagerInterface $entityManager): Response
    {
        $slideshowId = $slide->getSlideshow()->getId();
        
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

        return $this->redirectToRoute('app_slideshow_edit', ['id' => $slideshowId]);
    }

    #[Route('/reorder/{id}', name: 'app_slide_reorder', methods: ['POST'])]
    public function reorder(Request $request, Slideshow $slideshow, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['order'])) {
            return new JsonResponse(['success' => false, 'message' => 'Brak danych'], 400);
        }

        foreach ($data['order'] as $position => $slideId) {
            $slide = $entityManager->getRepository(Slide::class)->find($slideId);
            if ($slide && $slide->getSlideshow()->getId() === $slideshow->getId()) {
                $slide->setPosition($position);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
