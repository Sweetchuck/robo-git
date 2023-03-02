<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

trait OptionListPatternsTrait
{
    protected array $listPatterns = [];

    public function getListPatterns(): array
    {
        return $this->listPatterns;
    }

    public function setListPatterns(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->listPatterns = $value;

        return $this;
    }

    public function addListPatterns(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->listPatterns = $value + $this->listPatterns;

        return $this;
    }

    public function addListPattern(string $value): static
    {
        $this->listPatterns[$value] = true;

        return $this;
    }

    public function removeListPatterns(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, false);
        }

        $this->listPatterns = $value + $this->listPatterns;

        return $this;
    }

    public function removeListPattern(string $value): static
    {
        $this->listPatterns[$value] = false;

        return $this;
    }

    public function getOptionsListPatterns(): array
    {
        return [
            '--list' => [
                'type' => 'value:multi',
                'value' => $this->getListPatterns(),
            ],
        ];
    }
}
