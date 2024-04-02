<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Settings;

interface SettingsInterface
{
    public function get(string $key = ''): mixed;
}
