<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionAllTrait
{

    protected bool $all = false;

    public function getAll(): bool
    {
        return $this->all;
    }

    public function setAll(bool $value): static
    {
        $this->all = $value;

        return $this;
    }

    protected function getOptionsAll(): array
    {
        return [
            '--all' => [
                'type' => 'flag',
                'value' => $this->getAll(),
            ],
        ];
    }
}
