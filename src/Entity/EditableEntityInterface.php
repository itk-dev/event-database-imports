<?php

namespace App\Entity;

interface EditableEntityInterface
{
    public function isEditable(): bool;

    public function setEditable(bool $editable): static;
}
