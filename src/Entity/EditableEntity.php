<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adds editable field to an entity.
 */
trait EditableEntity
{
    #[ORM\Column]
    private bool $editable = false;

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
