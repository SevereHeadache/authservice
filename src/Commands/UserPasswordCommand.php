<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class UserPasswordCommand extends BaseCommand
{
    public const string ARGV = 'user:password';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Change user password');
        $this->cli->arguments->add([
            'name' => [
                'prefix' => 'n',
                'longPrefix' => 'name',
                'description' => 'Name',
                'required' => true,
            ],
            'password' => [
                'prefix' => 'p',
                'longPrefix' => 'password',
                'description' => 'New password',
                'requred' => true,
            ],
        ]);
    }

    public function process(): void
    {
        $name = $this->cli->arguments->get('name');
        $password = $this->cli->arguments->get('password');

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['name' => $name]);
        if (is_null($user)) {
            $this->cli->red("User \"$name\" not found.");

            return;
        }

        $user->setPassword($password);
        $this->entityManager->flush();

        $this->cli->lightGreen('Password changed successfully.');
    }
}
