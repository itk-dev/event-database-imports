<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location implements IndexItemInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $disabilityAccess = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $address = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Event::class)]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    public function toArray(): array
    {
        $address = $this->getAddress();

        return [
            'entityId' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'url' => $this->url,
            'telephone' => $this->telephone,
            'disabilityAccess' => $this->disabilityAccess,
            'mail' => $this->mail,
            'city' => $address?->getCity(),
            'street' => $address?->getStreet(),
            'suite' => $address?->getSuite(),
            'region' => $address?->getRegion(),
            'postalCode' => $address?->getPostalCode(),
            'country' => $address?->getCountry(),
            'coordinates' => [
                $address?->getLatitude(),
                $address?->getLongitude(),
            ],
        ];
    }
}
