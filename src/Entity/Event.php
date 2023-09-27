<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ticket_url = null;

    #[ORM\Column]
    private bool $public = true;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Feed $feed = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedItemId = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Occurrence::class, orphanRemoval: true)]
    private Collection $occurrences;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: DailyOccurrence::class, orphanRemoval: true)]
    private Collection $dailyOccurrences;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'events')]
    private Collection $tags;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Location $location = null;

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    #[ORM\OneToOne(inversedBy: 'event', cascade: ['persist', 'remove'])]
    private ?Image $image = null;

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
        $this->dailyOccurrences = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->ticket_url;
    }

    public function setTicketUrl(?string $ticket_url): static
    {
        $this->ticket_url = $ticket_url;

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
}
