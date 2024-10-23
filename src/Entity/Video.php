<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'liked')]
    private Collection $likers;

    #[ORM\Column(length: 255)]
    private ?string $video = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $basePosition = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $endingPosition = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    private ?User $instructor = null;

    #[ORM\Column]
    private ?bool $free = null;

    #[ORM\ManyToMany(targetEntity: Lesson::class, mappedBy: 'videos')]
    private Collection $lessons;

    public function __construct()
    {
        $this->likers = new ArrayCollection();
        $this->lessons = new ArrayCollection();
    }

    public const BASEPOSITION = [
        'Top Closed Guard',
        'Top Mount',
        'Top Side Control',
        'Bottom Closed Guard',
        'Bottom Mount',
        'Bottom Side Control',
        'Other',
    ];

    public const ENDINGPOSITION = [
        'Submission',
        'Defense',
        'Other',
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikers(): Collection
    {
        return $this->likers;
    }

    public function addLiker(User $liker): self
    {
        if (!$this->likers->contains($liker)) {
            $this->likers->add($liker);
            $liker->addLiked($this);
        }

        return $this;
    }

    public function removeLiker(User $liker): self
    {
        if ($this->likers->removeElement($liker)) {
            $liker->removeLiked($this);
        }

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(string $video): self
    {
        $this->video = $video;

        return $this;
    }
    public function getVideoUrl(): ?string
{
    $videoDirectory = 'videos/';
    $videoUrl = $videoDirectory . $this->getVideo();

    return $videoUrl;
}

    public function getBasePosition(): ?string
    {
        return $this->basePosition;
    }

    public function setBasePosition(string $basePosition): self
    {
        $this->basePosition = $basePosition;

        return $this;
    }

    public function getEndingPosition(): ?string
    {
        return $this->endingPosition;
    }

    public function setEndingPosition(string $endingPosition): self
    {
        $this->endingPosition = $endingPosition;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getInstructor(): ?User
    {
        return $this->instructor;
    }

    public function setInstructor(?User $instructor): self
    {
        $this->instructor = $instructor;

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(bool $free): self
    {
        $this->free = $free;

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->addVideo($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            $lesson->removeVideo($this);
        }

        return $this;
    }

}
