<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class UserResetCommand extends BaseCommand
{
    public const string ARGV = 'user:reset';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Reset login attempts');
        $this->cli->arguments->add([
            'name' => [
                'prefix' => 'n',
                'longPrefix' => 'name',
                'description' => 'Name',
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

        $user->resetAttempts();
        $this->entityManager->flush();

        $this->cli->lightGreen('Attempts reset.');
    }
}
