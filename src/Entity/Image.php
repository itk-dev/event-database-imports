<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\ImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedPath;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image implements EditableEntityInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use BlameableEntity;
    use EditableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Events->value])]
    #[SerializedPath('[original]')]
    private ?string $local = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    private ?Event $event = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function __toString(): string
    {
        return sprintf('%s (%d)', $this->title ?? 'Missing', $this->id ?? -1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(?string $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        // unset the owning side of the relation if necessary
        if (null === $event && null !== $this->event) {
            $this->event->setImage(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $event && $event->getImage() !== $this) {
            $event->setImage($this);
        }

        $this->event = $event;

        return $this;
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
}
