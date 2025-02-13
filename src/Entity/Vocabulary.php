<?php

namespace App\Entity;

use App\Model\Indexing\IndexNames;
use App\Repository\VocabularyRepository;
use App\Service\Indexing\IndexItemInterface;
use App\Service\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VocabularyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['name'],
    message: 'entity.vocabulary.name.not_unique')
]
#[UniqueEntity(
    fields: ['slug'],
    message: 'entity.vocabulary.slug.not_unique')
]
class Vocabulary implements IndexItemInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([IndexNames::Vocabularies->value, IndexNames::Tags->value])]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([IndexNames::Vocabularies->value, IndexNames::Tags->value])]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'vocabularies', cascade: ['persist'])]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Groups([IndexNames::Vocabularies->value])]
    private Collection $tags;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([IndexNames::Vocabularies->value])]
    private ?string $description = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s (%d)', $this->name ?? '', $this->id ?? -1);
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
