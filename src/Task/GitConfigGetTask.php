<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitConfigGetTask extends GitConfigTaskBase implements CommandInterface
{
    protected string $taskName = 'Git - Config get';

    // region name
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    // endregion

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $options['name'] = [
            'type' => 'arg-normal',
            'value' => $this->getName(),
        ];

        return $options;
    }

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }

        return $this;
    }

    protected function runProcessOutputs(): static
    {
        $name = $this->getName();
        $this->assets["git.config.$name"] = $this->actionExitCode === 0 ?
            trim($this->actionStdOutput, "\r\n")
            : null;

        return $this;
    }
}
