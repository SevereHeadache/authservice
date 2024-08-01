<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use DI\Attribute\Inject;
use SecurityLib\Strength;
use SevereHeadache\AuthService\Application\Settings\SettingsInterface;

class KeyGenerateCommand extends BaseCommand
{
    public const string ARGV = 'key:generate';

    #[Inject]
    protected SettingsInterface $settings;

    protected function init(): void
    {
        $this->cli->description('Generate signing key');
    }

    public function process(): void
    {
        $path = PROJECT_PATH . '/.env';
        if (!file_exists($path)) {
            $this->cli->red('".env" not found.');

            return;
        }

        $factory = new \RandomLib\Factory();
        $generator = $factory->getGenerator(new Strength(Strength::MEDIUM));
        file_put_contents($path, str_replace(
            'SECRET_KEY=' . $this->settings->get('app')['key'],
            'SECRET_KEY=' . $generator->generateString(64),
            file_get_contents($path),
        ));

        $this->cli->lightGreen('Generated.');
    }
}
