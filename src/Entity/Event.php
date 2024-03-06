<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\EventRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event implements IndexItemInterface, EditableEntityInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use BlameableEntity;
    use EditableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[entityId]')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([IndexNames::Events->value])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value])]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[ticketUrl]')]
    private ?string $ticketUrl = null;

    #[ORM\Column]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[publicAccess]')]
    private bool $public = true;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[organizer]')]
    private ?Organization $organization = null;

    #[ORM\ManyToMany(targetEntity: Organization::class, inversedBy: 'partnerEvents')]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[partners]')]
    private Collection $partners;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Feed $feed = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedItemId = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Occurrence::class, orphanRemoval: true)]
    #[Groups([IndexNames::Events->value])]
    private Collection $occurrences;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: DailyOccurrence::class, orphanRemoval: true)]
    #[Groups([IndexNames::Events->value])]
    private Collection $dailyOccurrences;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'events')]
    #[Groups([IndexNames::Events->value])]
    private Collection $tags;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Location $location = null;

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    #[ORM\OneToOne(inversedBy: 'event', cascade: ['persist', 'remove'])]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[imageUrls]')]
    private ?Image $image = null;

    #[Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[created]')]
    protected $createdAt;

    #[Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[updated]')]
    protected $updatedAt;

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
        $this->dailyOccurrences = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->partners = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%d)', $this->title ?? '', $this->id ?? -1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getTicketUrl(): ?string
    {
        return $this->ticketUrl;
    }

    public function setTicketUrl(?string $ticketUrl): static
    {
        $this->ticketUrl = $ticketUrl;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getFeed(): ?Feed
    {
        return $this->feed;
    }

    public function setFeed(?Feed $feed): static
    {
        $this->feed = $feed;

        return $this;
    }

    public function getFeedItemId(): ?string
    {
        return $this->feedItemId;
    }

    public function setFeedItemId(?string $feedItemId): static
    {
        $this->feedItemId = $feedItemId;

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
            $occurrence->setEvent($this);
        }

        return $this;
    }

    public function removeOccurrence(Occurrence $occurrence): static
    {
        if ($this->occurrences->removeElement($occurrence)) {
            // set the owning side to null (unless already changed)
            if ($occurrence->getEvent() === $this) {
                $occurrence->setEvent(null);
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
            $dailyOccurrence->setEvent($this);
        }

        return $this;
    }

    public function removeDailyOccurrence(DailyOccurrence $dailyOccurrence): static
    {
        if ($this->dailyOccurrences->removeElement($dailyOccurrence)) {
            // set the owning side to null (unless already changed)
            if ($dailyOccurrence->getEvent() === $this) {
                $dailyOccurrence->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

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

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Organization>
     */
    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(Organization $partner): static
    {
        if (!$this->partners->contains($partner)) {
            $this->partners->add($partner);
        }

        return $this;
    }

    public function removePartner(Organization $partner): static
    {
        $this->partners->removeElement($partner);

        return $this;
    }
}
