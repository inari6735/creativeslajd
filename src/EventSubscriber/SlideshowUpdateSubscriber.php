<?php

namespace App\EventSubscriber;

use App\Entity\Slide;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
class SlideshowUpdateSubscriber
{
    public function __construct(
        private HubInterface $hub
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            $this->publishSlideshowUpdate($entity, 'slide_added');
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            $this->publishSlideshowUpdate($entity, 'slide_removed');
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Slide) {
            $this->publishSlideshowUpdate($entity, 'slide_updated');
        }
    }

    private function publishSlideshowUpdate(Slide $slide, string $action): void
    {
        $slideshow = $slide->getSlideshow();
        if (!$slideshow) {
            return;
        }

        $topic = sprintf('slideshow/%d', $slideshow->getId());

        // Prepare slide data
        $slideData = [
            'id' => $slide->getId(),
            'imagePath' => $slide->getImagePath(),
            'position' => $slide->getPosition(),
        ];

        // Prepare all slides for complete update
        $allSlides = [];
        foreach ($slideshow->getSlides() as $s) {
            $allSlides[] = [
                'id' => $s->getId(),
                'imagePath' => $s->getImagePath(),
                'position' => $s->getPosition(),
            ];
        }

        // Sort by position
        usort($allSlides, fn($a, $b) => $a['position'] <=> $b['position']);

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
