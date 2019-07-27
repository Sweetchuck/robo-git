<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

use League\Container\ContainerAwareInterface;
use Psr\Log\LoggerAwareInterface;

trait GitTaskLoader
{
    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitBranchListTask
     */
    protected function taskGitBranchList(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitBranchListTask $task */
        $task = $this->task(Task\GitBranchListTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitCurrentBranchTask
     */
    protected function taskGitCurrentBranch(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitCurrentBranchTask $task */
        $task = $this->task(Task\GitCurrentBranchTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitListFilesTask
     */
    protected function taskGitListFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitListFilesTask $task */
        $task = $this->task(Task\GitListFilesTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitListChangedFilesTask
     */
    protected function taskGitListChangedFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitListChangedFilesTask $task */
        $task = $this->task(Task\GitListChangedFilesTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitListStagedFilesTask
     */
    protected function taskGitListStagedFiles(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitListStagedFilesTask $task */
        $task = $this->task(Task\GitListStagedFilesTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask
     */
    protected function taskGitNumOfCommitsBetween(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask $task */
        $task = $this->task(Task\GitNumOfCommitsBetweenTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask
     */
    protected function taskGitReadStagedFiles(array $options = [])
    {
        /** @var \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask $task */
        $task = $this->task(Task\GitReadStagedFilesTask::class);
        $this->injectDependenciesContainer($task);
        $this->injectDependenciesLogger($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitTagListTask
     */
    protected function taskGitTagList(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitTagListTask $task */
        $task = $this->task(Task\GitTagListTask::class);
        $this->injectDependenciesContainer($task);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @return \Robo\Collection\CollectionBuilder|Task\GitTopLevelTask
     */
    protected function taskGitTopLevel(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitTopLevelTask $task */
        $task = $this->task(Task\GitTopLevelTask::class);
        $task->setOptions($options);

        return $task;
    }

    /**
     * @param \League\Container\ContainerAwareInterface $child
     *
     * @return $this
     */
    protected function injectDependenciesContainer($child)
    {
        $container = $this instanceof ContainerAwareInterface ? $this->getContainer() : null;
        if ($container) {
            $child->setContainer($container);
        }

        return $this;
    }

    /**
     * @param \Psr\Log\LoggerAwareInterface $child
     *
     * @return $this
     */
    protected function injectDependenciesLogger($child)
    {
        $logger = $this instanceof LoggerAwareInterface ? $this->logger : null;
        if ($logger) {
            $child->setLogger($logger);
        }

        return $this;
    }
}
