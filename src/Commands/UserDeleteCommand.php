<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class UserDeleteCommand extends BaseCommand
{
    public const string ARGV = 'user:delete';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Delete user');
        $this->cli->arguments->add([
            'name' => [
                'prefix' => 'n',
                'longPrefix' => 'name',
                'description' => 'User name',
                'required' => true,
            ],
        ]);
    }

    public function process(): void
    {
        $name = $this->cli->arguments->get('name');

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['name' => $name]);
        if (is_null($user)) {
            $this->cli->red("User \"$name\" not found.");

            return;
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->cli->lightGreen("User \"$name\" deleted successfully.");
    }
}
