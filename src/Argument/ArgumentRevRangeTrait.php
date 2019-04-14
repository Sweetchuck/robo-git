<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Git\Argument;

trait ArgumentRevRangeTrait
{

    // region fromRevName
    /**
     * @var string
     */
    protected $fromRevName = '';

    public function getFromRevName(): string
    {
        return $this->fromRevName;
    }

    /**
     * @return $this
     */
    public function setFromRevName(string $fromRevName)
    {
        $this->fromRevName = $fromRevName;

        return $this;
    }
    // endregion

    // region toRevName
    /**
     * @var string
     */
    protected $toRevName = '';

    public function getToRevName(): string
    {
        return $this->toRevName;
    }

    /**
     * @return $this
     */
    public function setToRevName(string $toRevName)
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
                    'value' => sprintf('%s..%s', $this->getFromRevName(), $this->getToRevName()),
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
