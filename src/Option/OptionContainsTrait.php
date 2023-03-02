<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionContainsTrait
{
    protected ?bool $containsState = null;

    public function getContainsState(): ?bool
    {
        return $this->containsState;
    }

    public function setContainsState(?bool $value): static
    {
        $this->containsState = $value;

        return $this;
    }

    protected string $containsValue = '';

    public function getContainsValue(): string
    {
        return $this->containsValue;
    }

    public function setContainsValue(string $value): static
    {
        $this->containsValue = $value;

        return $this;
    }

    protected function getOptionsContains(): array
    {
        return [
            'contains' => [
                'type' => 'state:value-optional',
                'state' => $this->getContainsState(),
                'value' => $this->getContainsValue(),
            ],
        ];
    }
}
