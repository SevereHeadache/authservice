<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Commands\Cli;

use League\CLImate\TerminalObject\Dynamic\Checkbox\Checkbox;

class CheckboxGroup extends \League\CLImate\TerminalObject\Dynamic\Checkbox\CheckboxGroup
{
    public function __construct(array $options)
    {
        foreach ($options as $key => $option) {
            $checkbox = new Checkbox($option['value'], $key);
            if (isset($option['checked'])) {
                $checkbox->setChecked((bool) $option['checked']);
            }
            $this->checkboxes[] = $checkbox;
        }

        $this->count = count($this->checkboxes);

        $this->checkboxes[0]->setFirst()->setCurrent();
        $this->checkboxes[$this->count - 1]->setLast();
    }
}
