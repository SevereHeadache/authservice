<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\User;

class AccessListCommand extends BaseCommand
{
    public const string ARGV = 'access:list';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('List user\'s accesses');
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

        $table = [];
        foreach ($user->getAccesses() as $client) {
            $table[] = [
                'id' => $client->getId(),
                'name' => $client->getName(),
            ];
        }

        empty($table) ? $this->cli->yellow('Empty.') : $this->cli->table($table);
    }
}
