<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionColorTrait
{
    protected null|bool|string $color = null;

    public function getColor(): null|bool|string
    {
        return $this->color;
    }

    public function setColor(null|bool|string $value): static
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
