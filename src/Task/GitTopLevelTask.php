<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitTopLevelTask extends BaseTask implements CommandInterface
{
    protected string $taskName = 'Git - Top level';

    protected string $action = 'rev-parse';

    protected function getOptions(): array
    {
        $options = [
            '--show-toplevel' => [
                'type' => 'flag',
                'value' => true,
            ],
        ];

        return $options + parent::getOptions();
    }

    protected function runProcessOutputs(): static
    {
        $this->assets['git.topLevel'] = trim($this->actionStdOutput, "\r\n");

        return $this;
    }
}
