<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\OrganizationRepository;
use App\Service\Indexing\IndexItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization implements IndexItemInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    #[SerializedPath('[entityId]')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    #[SerializedPath('[email]')]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    private ?string $url = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organizations')]
    private Collection $Users;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Feed::class)]
    private Collection $feeds;

    #[Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    #[SerializedPath('[created]')]
    protected $createdAt;

    #[Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups([IndexNames::Events->value, IndexNames::Organization->value])]
    #[SerializedPath('[updated]')]
    protected $updatedAt;

    public function __construct()
    {
        $this->Users = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->feeds = new ArrayCollection();
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

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->Users;
    }

    public function addUser(User $user): static
    {
        if (!$this->Users->contains($user)) {
            $this->Users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->Users->removeElement($user);

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
            $event->setOrganization($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOrganization() === $this) {
                $event->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feed>
     */
    public function getFeeds(): Collection
    {
        return $this->feeds;
    }

    public function addFeed(Feed $feed): static
    {
        if (!$this->feeds->contains($feed)) {
            $this->feeds->add($feed);
            $feed->setOrganization($this);
        }

        return $this;
    }

    public function removeFeed(Feed $feed): static
    {
        if ($this->feeds->removeElement($feed)) {
            // set the owning side to null (unless already changed)
            if ($feed->getOrganization() === $this) {
                $feed->setOrganization(null);
            }
        }

        return $this;
    }
}
