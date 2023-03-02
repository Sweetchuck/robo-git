<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionMergedTrait
{
    protected ?bool $mergedState = null;

    public function getMergedState(): ?bool
    {
        return $this->mergedState;
    }

    public function setMergedState(?bool $value): static
    {
        $this->mergedState = $value;

        return $this;
    }

    protected string $mergedValue = '';

    public function getMergedValue(): string
    {
        return $this->mergedValue;
    }

    public function setMergedValue(string $value): static
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
