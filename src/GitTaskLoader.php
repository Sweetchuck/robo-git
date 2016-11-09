<?php

namespace Cheppers\Robo\Git;

use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

/**
 * Class GitTaskLoader.
 *
 * @package Cheppers\Robo\Git
 */
trait GitTaskLoader
{
    /**
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder|\Cheppers\Robo\Git\Task\ReadStagedFilesTask
     */
    protected function taskGitReadStagedFiles(array $options = null)
    {
        /** @var \Cheppers\Robo\Git\Task\ReadStagedFilesTask $task */
        $task = $this->task(Task\ReadStagedFilesTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder|\Cheppers\Robo\Git\Task\ListFilesTask
     */
    protected function taskGitListFiles(array $options = null)
    {
        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
        $task = $this->task(Task\ListFilesTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
