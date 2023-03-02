<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionPointsAtTrait
{
    protected string $pointsAt = '';

    public function getPointsAt(): string
    {
        return $this->pointsAt;
    }

    public function setPointsAt(string $value): static
    {
        $this->pointsAt = $value;

        return $this;
    }

    public function getOptionsPointsAt(): array
    {
        return [
            '--points-at' => [
                'type' => 'value:required',
                'value' => $this->getPointsAt(),
            ],
        ];
    }
}
