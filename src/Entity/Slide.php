<?php

namespace App\Entity;

use App\Repository\SlideRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlideRepository::class)]
class Slide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 20)]
    private string $mediaType = 'image';

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $youtubeUrl = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'slides')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Slideshow $slideshow = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSlideshow(): ?Slideshow
    {
        return $this->slideshow;
    }

    public function setSlideshow(?Slideshow $slideshow): static
    {
        $this->slideshow = $slideshow;

        return $this;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function isImage(): bool
    {
        return $this->mediaType === 'image';
    }

    public function isVideo(): bool
    {
        return $this->mediaType === 'video';
    }

    public function getYoutubeUrl(): ?string
    {
        return $this->youtubeUrl;
    }

    public function setYoutubeUrl(?string $youtubeUrl): static
    {
        $this->youtubeUrl = $youtubeUrl;

        return $this;
    }

    public function isYoutube(): bool
    {
        return $this->mediaType === 'youtube';
    }

    public function getYoutubeVideoId(): ?string
    {
        if (!$this->youtubeUrl) {
            return null;
        }

        // Parse YouTube URL to extract video ID
        // Supports: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID
        $pattern = '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        if (preg_match($pattern, $this->youtubeUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
