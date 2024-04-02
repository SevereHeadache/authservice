<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Core;

use DateTimeImmutable;
use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use SevereHeadache\AuthService\Domain\User;

class AuthService
{
    #[Inject]
    private EntityManagerInterface $entityManager;

    #[Inject]
    private Configuration $config;

    private User $user;

    public function authenticate(string $name, string $password): bool
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['name' => $name, 'access' => true]);
        if (is_null($user)) {
            return false;
        }
        if (!$user->verifyPassword($password)) {
            $user->incrementAttempts();
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return false;
        }
        if ($user->getAttemptsExhausted()) {
            return false;
        }

        $user->resetAttempts();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->user = $user;

        return true;
    }

    public function issueAccessToken(): string
    {
        $tokenBuilder = $this->config->builder(ChainedFormatter::default());
        $lifetime = env('TOKEN_LIFETIME');

        $now = new DateTimeImmutable();
        $accessToken = $tokenBuilder
            ->issuedBy(env('TOKEN_ISSUER'))
            ->relatedTo($this->user->getName())
            ->issuedAt($now)
            ->expiresAt($now->modify("+$lifetime seconds"))
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $accessToken->toString();
    }

    public function verifyAccessToken(string $rawAccessToken): bool
    {
        $parser = $this->config->parser();
        $accessToken = $parser->parse($rawAccessToken);

        $validator = $this->config->validator();
        $validated = $validator->validate(
            $accessToken,
            ...$this->config->validationConstraints(),
        );
        if (!$validated) {
            return false;
        }

        return true;
    }
}
