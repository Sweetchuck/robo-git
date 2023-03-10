<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

abstract class GitConfigTaskBase extends BaseTask implements CommandInterface
{
    protected string $taskName = 'Git - Config';

    protected string $action = 'config';

    // region source
    protected string $source = '';

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }
    // endregion

    // region stopOnFail
    protected bool $stopOnFail = true;

    public function getStopOnFail(): bool
    {
        return $this->stopOnFail;
    }

    public function setStopOnFail(bool $stopOnFail): static
    {
        $this->stopOnFail = $stopOnFail;

        return $this;
    }
    // endregion

    protected function getOptions(): array
    {
        $options = [];

        $source = $this->getSource();
        if (in_array($source, $this->getAllowedSources())) {
            $options["--$source"] = [
                'type' => 'flag',
                'value' => true,
            ];
        }

        return $options + parent::getOptions();
    }

    public function setOptions(array $options): static
    {
        if (array_key_exists('source', $options)) {
            $this->setSource($options['source']);
        }

        if (array_key_exists('stopOnFail', $options)) {
            $this->setStopOnFail($options['stopOnFail']);
        }

        return $this;
    }

    protected function getAllowedSources(): array
    {
        return ['local', 'system', 'global'];
    }

    protected function getTaskExitCode(): int
    {
        return $this->getStopOnFail() ? $this->actionExitCode : 0;
    }
}
