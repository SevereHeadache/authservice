<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use League\CLImate\Exceptions\InvalidArgumentException;
use SecurityLib\Strength;
use SevereHeadache\AuthService\Domain\User;

class UserCreateCommand extends BaseCommand
{
    public const string ARGV = 'user:create';

    #[Inject]
    private EntityManagerInterface $entityManager;

    protected function init(): void
    {
        $this->cli->description('Create user');
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
                'description' => 'Password',
            ],
            'generatePassword' => [
                'prefix' => 'g',
                'longPrefix' => 'generate',
                'description' => 'Generate password',
                'noValue' => true,
            ],
        ]);
    }

    public function process(): void
    {
        $name = $this->cli->arguments->get('name');
        if (strlen($name) > 50) {
            throw new InvalidArgumentException('[Name] is too long. Max 50 characters.');
        }
        $userRepository = $this->entityManager->getRepository(User::class);
        if (!is_null($userRepository->findOneBy(['name' => $name]))) {
            $this->cli->red('User already exists.');

            return;
        }

        $password = $this->cli->arguments->get('password');
        $generate = $this->cli->arguments->get('generatePassword');
        if ($password) {
            $pass = $password;
            $outputPass = false;
        } elseif ($generate) {
            $factory = new \RandomLib\Factory();
            $generator = $factory->getGenerator(new Strength(Strength::LOW));
            $pass = $generator->generateString(8);
            $outputPass = true;
        } else {
            throw new InvalidArgumentException('Either [Password] or [Generate] must be specified.');
        }

        $user = new User();
        $user->register($name, $pass);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->cli->lightGreen("User \"$name\" created.");
        if ($outputPass) {
            $this->cli->lightGreen("Password: \"$pass\".");
        }
    }
}
