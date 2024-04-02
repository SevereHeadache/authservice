<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class UserListCommand extends BaseCommand
{
    public const string ARGV = 'user:list';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('List users');
    }

    public function process(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();

        $table = [];
        foreach ($users as $user) {
            $table[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'attempts' => $user->getAttempts(),
                'blocked' => $user->getIsBlocked() ? 'yes' : 'no',
            ];
        }

        empty($table) ? $this->cli->yellow('Empty.') : $this->cli->table($table);
    }
}
