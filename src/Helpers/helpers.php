<?php

declare(strict_types=1);

function env(string $key, mixed $default = null): mixed
{
    if (!isset($_ENV[$key])) {
        return $default;
    }

    return $_ENV[$key];
}
