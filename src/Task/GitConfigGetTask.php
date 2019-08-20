<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitConfigGetTask extends BaseTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git - Config get';

    /**
     * {@inheritdoc}
     */
    protected $action = 'config';

    // region source
    /**
     * @var string
     */
    protected $source = '';

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return $this
     */
    public function setSource(string $source)
    {
        $this->source = $source;

        return $this;
    }
    // endregion

    // region name
    /**
     * @var string
     */
    protected $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }
    // endregion

    // region stopOnFail
    /**
     * @var bool
     */
    protected $stopOnFail = true;

    public function getStopOnFail(): bool
    {
        return $this->stopOnFail;
    }

    /**
     * @return $this
     */
    public function setStopOnFail(bool $stopOnFail)
    {
        $this->stopOnFail = $stopOnFail;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
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

        $options['name'] = [
            'type' => 'arg-normal',
            'value' => $this->getName(),
        ];

        return $options + parent::getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('source', $options)) {
            $this->setSource($options['source']);
        }

        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }

        if (array_key_exists('stopOnFail', $options)) {
            $this->setStopOnFail($options['stopOnFail']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        $name = $this->getName();
        $this->assets["git.config.$name"] = $this->actionExitCode === 0 ?
            trim($this->actionStdOutput, "\r\n")
            : null;

        return $this;
    }

    protected function getAllowedSources(): array
    {
        return ['local', 'system', 'global'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskExitCode(): int
    {
        return $this->getStopOnFail() ? $this->actionExitCode : 0;
    }
}
