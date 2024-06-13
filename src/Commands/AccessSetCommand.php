<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use SevereHeadache\AuthService\Domain\Client;
use SevereHeadache\AuthService\Domain\User;

class AccessSetCommand extends BaseCommand
{
    public const string ARGV = 'access:set';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Configure user\'s accesses');
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

        $clientRepository = $this->entityManager->getRepository(Client::class);
        $clients = $clientRepository->findAll();

        $options = [];
        foreach ($clients as $client) {
            $options[$client->getName()] = ['value' => $client->getName()];
        }
        foreach ($user->getAccesses() as $userClient) {
            foreach ($clients as $client) {
                if ($client->getName() === $userClient->getName()) {
                    $options[$userClient->getName()]['checked'] = true;

                    break;
                }
            }
        }

        $input = $this->cli->checkboxes('Available clients:', $options);
        $response = $input->prompt();

        $accesses = new ArrayCollection();
        foreach ($response as $clientName) {
            foreach ($clients as $client) {
                if ($client->getName() === $clientName) {
                    $accesses->add($client);

                    break;
                }
            }
        }

        $user->setAccesses($accesses);
        $this->entityManager->flush();
    }
}
