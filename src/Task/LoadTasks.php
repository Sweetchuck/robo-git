<?php

namespace Cheppers\Robo\Git\Task;

use Cheppers\Robo\Git\Task\ReadStagedFilesTask;
use League\Container\ContainerAwareInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Contract\OutputAwareInterface;

/**
 * Class PhpcsTask.
 *
 * @package Cheppers\Robo\Phpcs\Task
 */
trait LoadTasks
{
    /**
     * Expose phpcs-lint task.
     *
     * @param array $options
     *
     * @return CollectionBuilder | \Cheppers\Robo\Git\Task\ReadStagedFilesTask
     */
    protected function taskGitReadStagedFiles(array $options = null)
    {
        /** @var ReadStagedFilesTask $task */
        $task = $this->task(ReadStagedFilesTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
