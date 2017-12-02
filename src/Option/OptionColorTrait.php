<?php

namespace Sweetchuck\Robo\Git\Option;

trait OptionColorTrait
{
    /**
     * @var bool|null|string
     */
    protected $color = null;

    /**
     * @return bool|null|string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param bool|null|string $value
     *
     * @return $this
     */
    public function setColor($value)
    {
        $this->color = $value;

        return $this;
    }

    public function getOptionsColor(): array
    {
        return [
            'color' => [
                'type' => 'flag:true-value',
                'value' => $this->getColor(),
            ],
        ];
    }
}
