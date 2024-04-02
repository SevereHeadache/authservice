<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Application\Settings;

class Settings implements SettingsInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function get(string $key = ''): mixed
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
    }
}
