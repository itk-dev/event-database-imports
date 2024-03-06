<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address implements EditableEntityInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use EditableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $street = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $suite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $country = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?string $postalCode = null;

    #[ORM\Column(nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups([IndexNames::Events->value, IndexNames::Locations->value])]
    private ?float $longitude = null;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Location::class)]
    private Collection $locations;

    #[ORM\Column]
    private bool $editable = false;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s, %s (%d)', $this->street ?? '', $this->city ?? '', $this->id ?? -1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getSuite(): ?string
    {
        return $this->suite;
    }

    public function setSuite(string $suite): static
    {
        $this->suite = $suite;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setAddress($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->locations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getAddress() === $this) {
                $location->setAddress(null);
            }
        }

        return $this;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function setEditable(bool $editable): static
    {
        $this->editable = $editable;

        return $this;
    }
}
