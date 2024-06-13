<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\Client;

class ClientListCommand extends BaseCommand
{
    public const string ARGV = 'client:list';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('List clients');
    }

    public function process(): void
    {
        $clientRepository = $this->entityManager->getRepository(Client::class);
        $clients = $clientRepository->findAll();

        $table = [];
        foreach ($clients as $client) {
            $table[] = [
                'id' => $client->getId(),
                'name' => $client->getName(),
            ];
        }

        empty($table) ? $this->cli->yellow('Empty.') : $this->cli->table($table);
    }
}
