<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionMergedTrait
{
    /**
     * @var null|bool
     */
    protected $mergedState = null;

    public function getMergedState(): ?bool
    {
        return $this->mergedState;
    }

    /**
     * @return $this
     */
    public function setMergedState(?bool $value)
    {
        $this->mergedState = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $mergedValue = '';

    public function getMergedValue(): string
    {
        return $this->mergedValue;
    }

    /**
     * @return $this
     */
    public function setMergedValue(string $value)
    {
        $this->mergedValue = $value;

        return $this;
    }

    protected function getOptionsMerged(): array
    {
        return [
            'merged' => [
                'type' => 'state:value-optional',
                'state' => $this->getMergedState(),
                'value' => $this->getMergedValue(),
            ],
        ];
    }
}
