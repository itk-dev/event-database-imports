<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\OccurrenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OccurrenceRepository::class)]
#[ApiResource]
class Occurrence
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $end = null;

    #[ORM\Column(length: 255)]
    private ?string $ticketPriceRange = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $room = null;

    #[ORM\ManyToOne(inversedBy: 'occurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\OneToMany(mappedBy: 'occurrence', targetEntity: DailyOccurrence::class, orphanRemoval: true)]
    private Collection $dailyOccurrences;

    #[ORM\ManyToOne(inversedBy: 'occurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    public function __construct()
    {
        $this->dailyOccurrences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeImmutable $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(\DateTimeImmutable $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getTicketPriceRange(): ?string
    {
        return $this->ticketPriceRange;
    }

    public function setTicketPriceRange(string $ticketPriceRange): static
    {
        $this->ticketPriceRange = $ticketPriceRange;

        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(?string $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, DailyOccurrence>
     */
    public function getDailyOccurrences(): Collection
    {
        return $this->dailyOccurrences;
    }

    public function addDailyOccurrence(DailyOccurrence $dailyOccurrence): static
    {
        if (!$this->dailyOccurrences->contains($dailyOccurrence)) {
            $this->dailyOccurrences->add($dailyOccurrence);
            $dailyOccurrence->setOccurrence($this);
        }

        return $this;
    }

    public function removeDailyOccurrence(DailyOccurrence $dailyOccurrence): static
    {
        if ($this->dailyOccurrences->removeElement($dailyOccurrence)) {
            // set the owning side to null (unless already changed)
            if ($dailyOccurrence->getOccurrence() === $this) {
                $dailyOccurrence->setOccurrence(null);
            }
        }

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }
}
