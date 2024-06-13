<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use League\CLImate\Exceptions\InvalidArgumentException;
use SevereHeadache\AuthService\Domain\Client;

class ClientCreateCommand extends BaseCommand
{
    public const string ARGV = 'client:create';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Create client');
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
        if (strlen($name) > 255) {
            throw new InvalidArgumentException('[Name] is too long. Max 255 characters.');
        }
        $clientRepository = $this->entityManager->getRepository(Client::class);
        if (!is_null($clientRepository->findOneBy(['name' => $name]))) {
            $this->cli->red('Client already exists.');

            return;
        }

        $client = new Client();
        $client->setName($name);
        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->cli->lightGreen("Client \"$name\" created.");
    }
}
