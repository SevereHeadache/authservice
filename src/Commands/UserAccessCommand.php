<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class UserAccessCommand extends BaseCommand
{
    public const string ARGV = 'user:access';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Manage user access');
        $this->cli->arguments->add([
            'name' => [
                'prefix' => 'n',
                'longPrefix' => 'name',
                'description' => 'User name',
                'required' => true,
            ],
            'access' => [
                'prefix' => 'a',
                'longPrefix' => 'access',
                'description' => 'Block[0]/unblock[1] user',
                'castTo' => 'bool',
                'defaultValue' => true,
            ],
        ]);
    }

    public function process(): void
    {
        $name = $this->cli->arguments->get('name');
        $access = $this->cli->arguments->get('access');

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['name' => $name]);
        if (is_null($user)) {
            $this->cli->red("User \"$name\" not found.");

            return;
        }

        $user->setAccess($access);
        $this->entityManager->flush();

        $this->cli->lightGreen('User ' . ($access ? 'unblocked' : 'blocked') . '.');
    }
}
