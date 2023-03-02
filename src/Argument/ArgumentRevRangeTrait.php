<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Argument;

trait ArgumentRevRangeTrait
{

    // region fromRevName
    protected string $fromRevName = '';

    public function getFromRevName(): string
    {
        return $this->fromRevName;
    }

    public function setFromRevName(string $fromRevName): static
    {
        $this->fromRevName = $fromRevName;

        return $this;
    }
    // endregion

    // region toRevName
    protected string $toRevName = '';

    public function getToRevName(): string
    {
        return $this->toRevName;
    }

    public function setToRevName(string $toRevName): static
    {
        $this->toRevName = $toRevName;

        return $this;
    }
    // endregion

    public function getArgumentRevRange(): array
    {
        $from = $this->getFromRevName();
        $to = $this->getToRevName();

        if (!$from && !$to) {
            return [];
        }

        if ($from && $to) {
            return [
                'argument:revRange' => [
                    'type' => 'arg-normal',
                    'value' => "$from..$to",
                ],
            ];
        }

        return [
            'argument:revRange' => [
                'type' => 'arg-normal',
                'value' => $from ?: $to,
            ],
        ];
    }
}
