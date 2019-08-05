<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitCurrentBranchTask extends BaseTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git current branch';

    /**
     * {@inheritdoc}
     */
    protected $action = 'symbolic-ref';

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
