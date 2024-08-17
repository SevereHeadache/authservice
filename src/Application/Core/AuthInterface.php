<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Core;

interface AuthInterface
{
    public function authenticate(string $name, string $password): bool;

    public function issueAccessToken(): string;

    public function verifyAccessToken(string $rawAccessToken, string $client): bool;
}
