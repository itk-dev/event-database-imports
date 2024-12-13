<?php

namespace App\Entity;

use App\Controller\Admin\DashboardController;
use App\Model\Indexing\IndexNames;
use App\Repository\DailyOccurrenceRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: DailyOccurrenceRepository::class)]
class DailyOccurrence implements IndexItemInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    #[SerializedPath('[entityId]')]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    private ?\DateTimeImmutable $end = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    private ?string $ticketPriceRange = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    private ?string $room = null;

    #[ORM\ManyToOne(inversedBy: 'dailyOccurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'dailyOccurrences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Occurrence $occurrence = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Occurrences->value])]
    private ?string $status = null;

    public function __toString(): string
    {
        // @TODO find a way to do this better and avoid leaking EasyAdmin stuff into the entity model!
        // In EasyAdmin the value used for display is chosen in EasyAdminTwigExtension::representAsString()
        // There doesn't seem to be a way to change this from any of the Easyadmin option or filters. In the
        // case of using CollectionField this means the __toString() is called :-(

        $viewTimezone = new \DateTimeZone(DashboardController::VIEW_TIMEZONE);
        $start = $this->start?->setTimezone($viewTimezone);
        $end = $this->end?->setTimezone($viewTimezone);

        $viewTimezone = new \DateTimeZone(DashboardController::VIEW_TIMEZONE);
        $format = 'Y-m-d H:i';
        $start?->setTimezone($viewTimezone);
        $end?->setTimezone($viewTimezone);

        return $start?->format($format).
            ' - '.$end?->format($format).
            ' / '.$this->getTicketPriceRange().
            ' / '.$this->getRoom();
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

    public function getOccurrence(): ?Occurrence
    {
        return $this->occurrence;
    }

    public function setOccurrence(?Occurrence $occurrence): static
    {
        $this->occurrence = $occurrence;

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
