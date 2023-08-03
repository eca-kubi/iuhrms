<?php

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModelsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getFullName', [$this, 'getFullName']),
            new TwigFunction('getInitials', [$this, 'getInitials']),
        ];
    }

    public function getFullName(UserModel $user): string
    {
        return $user->getFullName();
    }

    public function getInitials(UserModel $user): string
    {
        return $user->getInitials();
    }
}