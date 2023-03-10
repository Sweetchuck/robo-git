<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\Enum\GitConfigExitCode;

class GitConfigSetTask extends GitConfigTaskBase implements CommandInterface
{
    protected string $taskName = 'Git - Config set';

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

    // region value
    protected ?string $value = null;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
    // endregion

    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $value = $this->getValue();
        if ($value === null) {
            $options['--unset'] = [
                'type' => 'flag',
                'value' => true,
            ];
        }

        $options['name'] = [
            'type' => 'arg-normal',
            'value' => $this->getName(),
        ];

        if ($value !== null) {
            $options['value'] = [
                'type' => 'arg-normal',
                'value' => $value,
            ];
        }

        return $options;
    }

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }

        if (array_key_exists('value', $options)) {
            $this->setValue($options['value']);
        }

        return $this;
    }

    protected function getTaskExitCode(): int
    {
        if ($this->getValue() === null
            && $this->actionExitCode === GitConfigExitCode::NameNotExists->value
        ) {
            return 0;
        }

        return $this->getStopOnFail() ?
            $this->actionExitCode
            : 0;
    }
}
