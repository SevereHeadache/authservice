<?php

namespace SevereHeadache\AuthService\Commands\Cli;

class Checkboxes extends \League\CLImate\TerminalObject\Dynamic\Checkboxes
{
    protected function buildCheckboxes(array $options)
    {
        return new CheckboxGroup($options);
    }
}
