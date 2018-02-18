<?php

namespace Sweetchuck\Robo\Git\Task;

class GitTopLevelTask extends BaseTask
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git - Top level';

    /**
     * {@inheritdoc}
     */
    protected $action = 'rev-parse';

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

    protected function runProcessOutputs()
    {
        $this->assets['git.topLevel'] = trim($this->actionStdOutput, "\r\n");

        return $this;
    }
}
