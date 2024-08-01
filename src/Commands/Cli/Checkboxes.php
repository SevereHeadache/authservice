<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands\Cli;

class Checkboxes extends \League\CLImate\TerminalObject\Dynamic\Checkboxes
{
    protected function buildCheckboxes(array $options)
    {
        return new CheckboxGroup($options);
    }
}
