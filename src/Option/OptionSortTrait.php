<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionSortTrait
{
    protected string $sort = '';

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $value): static
    {
        $this->sort = $value;

        return $this;
    }

    public function getOptionsSort(): array
    {
        return [
            '--sort' => [
                'type' => 'value:required',
                'value' => $this->getSort(),
            ],
        ];
    }
}
