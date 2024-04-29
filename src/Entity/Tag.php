<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\TagRepository;
use App\Service\Indexing\IndexItemInterface;
use App\Service\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tag implements IndexItemInterface, EditableEntityInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use EditableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Vocabulary::class, mappedBy: 'tags')]
    #[Groups([IndexNames::Tags->value])]
    private Collection $vocabularies;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([IndexNames::Tags->value, IndexNames::Vocabularies->value, IndexNames::Events->value])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([IndexNames::Tags->value, IndexNames::Vocabularies->value, IndexNames::Events->value])]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'tags')]
    private Collection $events;

    public function __construct()
    {
        $this->vocabularies = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Vocabulary>
     */
    public function getVocabularies(): Collection
    {
        return $this->vocabularies;
    }

    public function addVocabulary(Vocabulary $vocabulary): static
    {
        if (!$this->vocabularies->contains($vocabulary)) {
            $this->vocabularies->add($vocabulary);
            $vocabulary->addTag($this);
        }

        return $this;
    }

    public function removeVocabulary(Vocabulary $vocabulary): static
    {
        if ($this->vocabularies->removeElement($vocabulary)) {
            $vocabulary->removeTag($this);
        }

        return $this;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSlug(): static
    {
        $this->slug = Slugger::slugify($this->name);

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
            $event->addTag($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeTag($this);
        }

        return $this;
    }
}
