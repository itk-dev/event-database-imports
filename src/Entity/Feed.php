<?php

namespace App\Entity;

use App\Repository\FeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: FeedRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Feed
{
    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $configuration = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastRead = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\ManyToOne(inversedBy: 'feeds')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'feed', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\ManyToOne(inversedBy: 'feeds')]
    private ?Organization $organization = null;

    private ?string $tmpConfig = null;

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

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Helper function to display data in code-editor in easy admin.
     */
    public function getConfigurationField(): string
    {
        return json_encode($this->configuration, JSON_PRETTY_PRINT);
    }

    /**
     * Helper function store data from code-editor in easy admin.
     *
     * @throws \InvalidArgumentException
     */
    public function setConfigurationField(string $json): static
    {
        $this->tmpConfig = $json;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        if (null !== $this->tmpConfig) {
            if (false === \json_validate($this->tmpConfig)) {
                $context->buildViolation('Json error: '.\json_last_error_msg())->atPath('configuration')->addViolation();
            }
        }
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setConfigurationValue(): void
    {
        if (null !== $this->tmpConfig) {
            try {
                $this->configuration = json_decode($this->tmpConfig, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \InvalidArgumentException(\json_last_error_msg(), \json_last_error(), $e);
            }
        }
    }

    public function getLastRead(): ?\DateTimeImmutable
    {
        return $this->lastRead;
    }

    public function setLastRead(\DateTimeImmutable $lastRead): static
    {
        $this->lastRead = $lastRead;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $event->setFeed($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getFeed() === $this) {
                $event->setFeed(null);
            }
        }

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
}
