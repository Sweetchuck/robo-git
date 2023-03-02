<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Argument;

trait ArgumentPathsTrait
{
    protected array $paths = [];

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function setPaths(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->paths = $value;

        return $this;
    }

    public function addPaths(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->paths = $value + $this->paths;

        return $this;
    }

    public function addPath(string $value): static
    {
        $this->paths[$value] = true;

        return $this;
    }

    public function removePaths(array $value): static
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, false);
        }

        $this->paths = $value + $this->paths;

        return $this;
    }

    public function removePath(string $value): static
    {
        $this->paths[$value] = false;

        return $this;
    }

    public function getArgumentPaths(): array
    {
        return [
            'argument:paths' => [
                'type' => 'arg-extra:list',
                'value' => $this->getPaths(),
            ],
        ];
    }
}
