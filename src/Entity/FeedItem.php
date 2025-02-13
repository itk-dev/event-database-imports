<?php

namespace App\Entity;

use App\Repository\FeedItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: FeedItemRepository::class)]
#[ORM\UniqueConstraint(name: 'feed_feedItemId_unique', columns: ['feed_id', 'feed_item_id'])]
#[UniqueEntity(
    fields: ['feed', 'feedItemId'],
    message: 'entity.feed_item.feed_feedItemId_unique'
)]
class FeedItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'feedItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Feed $feed = null;

    #[ORM\OneToOne(inversedBy: 'feedItem', cascade: ['persist', 'remove'])]
    private ?Event $event = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feedItemId = null;

    #[ORM\Column]
    private array $data = [];

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column]
    #[Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastSeenAt = null;

    public function __construct(Feed $feed, string $feedItemId, array $data)
    {
        $this->feed = $feed;
        $this->feedItemId = $feedItemId;
        $this->setData($data);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

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

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(): static
    {
        $this->lastSeenAt = new \DateTimeImmutable();

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        $this->hash = $this->calculateHash();

        return $this;
    }

    private function calculateHash(): string
    {
        return hash('sha256', serialize($this->data));
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }
}
