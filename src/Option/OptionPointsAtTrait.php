<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionPointsAtTrait
{
    /**
     * @var string
     */
    protected $pointsAt = '';

    public function getPointsAt(): string
    {
        return $this->pointsAt;
    }

    /**
     * @return $this
     */
    public function setPointsAt(string $value)
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
