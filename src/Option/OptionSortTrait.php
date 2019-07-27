<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionSortTrait
{
    /**
     * @var string
     */
    protected $sort = '';

    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @return $this
     */
    public function setSort(string $value)
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
