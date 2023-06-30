<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ApiResource]
class Location
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column]
    private ?bool $disabilityAccess = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mail = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $address = null;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Occurrence::class)]
    private Collection $occurrences;

    #[ORM\OneToMany(mappedBy: 'location', targetEntity: DailyOccurrence::class)]
    private Collection $dailyOccurrences;

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
        $this->dailyOccurrences = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

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
     * @return Collection<int, Occurrence>
     */
    public function getOccurrences(): Collection
    {
        return $this->occurrences;
    }

    public function addOccurrence(Occurrence $occurrence): static
    {
        if (!$this->occurrences->contains($occurrence)) {
            $this->occurrences->add($occurrence);
            $occurrence->setLocation($this);
        }

        return $this;
    }

    public function removeOccurrence(Occurrence $occurrence): static
    {
        if ($this->occurrences->removeElement($occurrence)) {
            // set the owning side to null (unless already changed)
            if ($occurrence->getLocation() === $this) {
                $occurrence->setLocation(null);
            }
        }

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
            $dailyOccurrence->setLocation($this);
        }

        return $this;
    }

    public function removeDailyOccurrence(DailyOccurrence $dailyOccurrence): static
    {
        if ($this->dailyOccurrences->removeElement($dailyOccurrence)) {
            // set the owning side to null (unless already changed)
            if ($dailyOccurrence->getLocation() === $this) {
                $dailyOccurrence->setLocation(null);
            }
        }

        return $this;
    }
}
