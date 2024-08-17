<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Core;

use DateTimeImmutable;
use DI\Attribute\Inject;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use SevereHeadache\AuthService\Domain\User;

class AuthService implements AuthInterface
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
        $audiences = [];
        foreach ($this->user->getAccesses() as $client) {
            $audiences[] = $client->getName();
        }

        $accessToken = $tokenBuilder
            ->issuedBy(env('TOKEN_ISSUER'))
            ->permittedFor(...$audiences)
            ->relatedTo($this->user->getName())
            ->issuedAt($now)
            ->expiresAt($now->modify("+$lifetime seconds"))
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $accessToken->toString();
    }

    public function verifyAccessToken(string $rawAccessToken, string $client): bool
    {
        $parser = $this->config->parser();
        try {
            $accessToken = $parser->parse($rawAccessToken);
        } catch (CannotDecodeContent) {
            return false;
        }

        $validator = $this->config->validator();
        $validated = $validator->validate(
            $accessToken,
            new PermittedFor($client),
            ...$this->config->validationConstraints(),
        );
        if (!$validated) {
            return false;
        }

        return true;
    }
}
