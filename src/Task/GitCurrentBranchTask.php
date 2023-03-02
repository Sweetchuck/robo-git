<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitCurrentBranchTask extends BaseTask implements CommandInterface
{
    protected string $taskName = 'Git current branch';

    protected string $action = 'symbolic-ref';

    protected array $assets = [
        'gitCurrentBranch.long' => null,
        'gitCurrentBranch.short' => null,
    ];

    protected function getOptions(): array
    {
        return [
            'ref-name' => [
                'type' => 'arg-normal',
                'value' => 'HEAD',
            ],
        ] + parent::getOptions();
    }

    protected function runProcessOutputs(): static
    {
        if ($this->actionExitCode === 0) {
            $branchName = trim($this->actionStdOutput);
            $this->assets['gitCurrentBranch.long'] = $branchName;
            $this->assets['gitCurrentBranch.short'] = preg_replace(
                '@^refs/heads/@',
                '',
                $branchName
            );
        }

        return $this;
    }
}
