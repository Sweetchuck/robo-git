<?php

namespace Sweetchuck\Robo\Git;

use League\Container\ContainerAwareInterface;
use Robo\Contract\OutputAwareInterface;

trait GitTaskLoader
{
    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitListFilesTask
     */
    protected function taskGitListFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitListFilesTask $task */
        $task = $this->task(Task\GitListFilesTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask
     */
    protected function taskGitReadStagedFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask $task */
        $task = $this->task(Task\GitReadStagedFilesTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitTagListTask
     */
    protected function taskGitTagList(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitTagListTask $task */
        $task = $this->task(Task\GitTagListTask::class, $options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask
     */
    protected function taskGitNumOfCommitsBetween(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask $task */
        $task = $this->task(Task\GitNumOfCommitsBetweenTask::class);
        $task->setOptions($options);
        if ($this instanceof ContainerAwareInterface) {
            $task->setContainer($this->getContainer());
        }

        if ($this instanceof OutputAwareInterface) {
            $task->setOutput($this->output());
        }

        return $task;
    }
}
