<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\LocationRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location implements IndexItemInterface, EditableEntityInterface
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

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $telephone = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[disabilityAccess]')]
    private ?bool $disabilityAccess = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $mail = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([IndexNames::Events->value])]
    private ?Address $address = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Event::class)]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%d)', $this->name ?? '', $this->id ?? -1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isDisabilityAccess(): ?bool
    {
        return $this->disabilityAccess;
    }

    public function setDisabilityAccess(bool $disabilityAccess): static
    {
        $this->disabilityAccess = $disabilityAccess;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setLocation($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getLocation() === $this) {
                $event->setLocation(null);
            }
        }

        return $this;
    }
}
