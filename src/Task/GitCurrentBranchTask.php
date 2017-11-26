<?php

namespace Sweetchuck\Robo\Git\Task;

class GitCurrentBranchTask extends GitSymbolicRefTask
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git current branch';

    /**
     * @var array
     */
    protected $assets = [
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

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
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
