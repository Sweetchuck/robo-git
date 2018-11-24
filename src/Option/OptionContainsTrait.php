<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionContainsTrait
{
    /**
     * @var null|bool
     */
    protected $containsState = null;

    public function getContainsState(): ?bool
    {
        return $this->containsState;
    }

    /**
     * @return $this
     */
    public function setContainsState(?bool $value)
    {
        $this->containsState = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $containsValue = '';

    public function getContainsValue(): string
    {
        return $this->containsValue;
    }

    /**
     * @return $this
     */
    public function setContainsValue(string $value)
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
