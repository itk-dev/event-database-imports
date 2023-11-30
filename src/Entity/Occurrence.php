<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\OccurrenceRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: OccurrenceRepository::class)]
class Occurrence implements IndexItemInterface, EditableEntityInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use EditableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[entityId]')]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    private ?\DateTimeImmutable $end = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $ticketPriceRange = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $room = null;

    #[ORM\ManyToOne(inversedBy: 'occurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\OneToMany(mappedBy: 'occurrence', targetEntity: DailyOccurrence::class, orphanRemoval: true)]
    private Collection $dailyOccurrences;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $status = null;

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

    public function setTicketPriceRange(?string $ticketPriceRange): static
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
