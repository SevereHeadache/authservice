<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands;

use League\CLImate\CLImate;
use League\CLImate\Exceptions\InvalidArgumentException;

abstract class BaseCommand
{
    public const string ARGV = '';

    public function __construct(protected CLImate $cli)
    {
        $this->init();
    }

    abstract protected function init(): void;

    public function help(): void
    {
        $this->cli->usage([static::ARGV]);
    }

    abstract protected function process(): void;

    public function run(): void
    {
        try {
            $this->cli->arguments->parse();
            $this->process();
        } catch (InvalidArgumentException $e) {
            $this->cli->to('error')->red($e->getMessage());
        }
    }
}
