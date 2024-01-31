<?php

namespace App\Security;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    public function getId(): int;

    public function getMail(): string;

    public function setEnabled(bool $enabled): static;

    public function setIsVerified(bool $isVerified): self;
}
