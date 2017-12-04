<?php

namespace Sweetchuck\Robo\Git\Option;

trait OptionAllTrait
{

    /**
     * @var bool
     */
    protected $all = false;

    public function getAll(): bool
    {
        return $this->all;
    }

    /**
     * @return $this
     */
    public function setAll(bool $value)
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
