<?php

namespace App\EventSubscriber;

use App\Entity\Slide;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postFlush)]
class SlideshowUpdateSubscriber
{
    private array $slidesToUpdate = [];

    public function __construct(
        private HubInterface $hub,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            error_log('📌 postPersist: Slide ID=' . $entity->getId() . ', Position=' . $entity->getPosition());
            $this->slidesToUpdate[] = ['slide' => $entity, 'action' => 'slide_added'];
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            $this->slidesToUpdate[] = ['slide' => $entity, 'action' => 'slide_removed'];
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            $this->slidesToUpdate[] = ['slide' => $entity, 'action' => 'slide_updated'];
        }
    }

    public function postFlush(): void
    {
        error_log('🔥 postFlush: Processing ' . count($this->slidesToUpdate) . ' slide updates');
        
        foreach ($this->slidesToUpdate as $item) {
            error_log('📤 Publishing: ' . $item['action'] . ' for slide ID=' . $item['slide']->getId());
            $this->publishSlideshowUpdate($item['slide'], $item['action']);
        }
        
        $this->slidesToUpdate = [];
    }

    private function publishSlideshowUpdate(Slide $slide, string $action): void
    {
        $slideshow = $slide->getSlideshow();
        if (!$slideshow) {
            error_log('❌ No slideshow for slide ID=' . $slide->getId());
            return;
        }

        // Get fresh slides directly from database
        $allSlidesEntities = $this->entityManager->getRepository(Slide::class)
            ->findBy(['slideshow' => $slideshow], ['position' => 'ASC']);
        
        error_log('📊 Slideshow has ' . count($allSlidesEntities) . ' slides (from DB)');
        
        $topic = sprintf('slideshow/%d', $slideshow->getId());

        // Prepare slide data
        $slideData = [
            'id' => $slide->getId(),
            'imagePath' => $slide->getImagePath(),
            'mediaType' => $slide->getMediaType(),
            'position' => $slide->getPosition(),
        ];

        // Prepare all slides for complete update
        $allSlides = [];
        foreach ($allSlidesEntities as $s) {
            $allSlides[] = [
                'id' => $s->getId(),
                'imagePath' => $s->getImagePath(),
                'mediaType' => $s->getMediaType(),
                'position' => $s->getPosition(),
            ];
        }

        $data = [
            'action' => $action,
            'slideshowId' => $slideshow->getId(),
            'slide' => $slideData,
            'allSlides' => $allSlides,
            'totalSlides' => count($allSlides),
            'timestamp' => time(),
        ];

        $update = new Update(
            $topic,
            json_encode($data),
            false // not private
        );

        try {
            $this->hub->publish($update);
        } catch (\Exception $e) {
            // Log error but don't break the application
            error_log('Failed to publish Mercure update: ' . $e->getMessage());
        }
    }
}
