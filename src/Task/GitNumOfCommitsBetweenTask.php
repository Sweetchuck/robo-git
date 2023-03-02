<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitNumOfCommitsBetweenTask extends BaseTask implements CommandInterface
{
    protected string $taskName = 'Git number of commits between';

    protected string $action = 'rev-list';

    protected array $assets = [
        'numOfCommits' => null,
    ];

    // region Option - fromRevName.
    protected string $fromRevName = '';

    public function getFromRevName(): string
    {
        return $this->fromRevName;
    }

    public function setFromRevName(string $value): static
    {
        $this->fromRevName = $value;

        return $this;
    }
    // endregion

    // region Option - toRevName.
    protected string $toRevName = 'HEAD';

    public function getToRevName(): string
    {
        return $this->toRevName;
    }

    public function setToRevName(string $value): static
    {
        $this->toRevName = $value;

        return $this;
    }
    // endregion

    protected function getOptions(): array
    {
        return [
            '--count' => [
                'type' => 'flag',
                'value' => true,
            ],
            'range' => [
                'type' => 'arg-normal',
                'value' => sprintf('%s..%s', $this->getFromRevName(), $this->getToRevName()),
            ],
        ] + parent::getOptions();
    }

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('fromRevName', $options)) {
            $this->setFromRevName($options['fromRevName']);
        }

        if (array_key_exists('toRevName', $options)) {
            $this->setToRevName($options['toRevName']);
        }

        return  $this;
    }

    protected function runValidate(): static
    {
        if (!$this->getFromRevName()) {
            throw new \Exception('The "from" rev name is missing', 1);
        }

        if (!$this->getToRevName()) {
            throw new \Exception('The "to" rev name is missing', 1);
        }

        return parent::runValidate();
    }

    protected function runProcessOutputs(): static
    {
        $this->assets['numOfCommits'] = (int) trim($this->actionStdOutput);

        return $this;
    }
}
