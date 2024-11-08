<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new Exception('Not a User instance.');
        }

        if ($user->isDeleted()) {
            throw new LockedException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
