<?php

declare(strict_types=1);

namespace App\Entity;

interface EditableEntityInterface
{
    public function isEditable(): bool;

    public function setEditable(bool $editable): static;
}
