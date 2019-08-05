<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

use League\Container\ContainerAwareInterface;
use Psr\Log\LoggerAwareInterface;

trait GitComboTaskLoader
{
    /**
     * @return \Robo\Collection\CollectionBuilder|\Sweetchuck\Robo\Git\Task\GitCloneAndCleanTask
     */
    protected function taskGitCloneAndClean(array $options = [])
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitCloneAndCleanTask $task */
        $task = $this->task(Task\GitCloneAndCleanTask::class);

        $container = $this instanceof ContainerAwareInterface ? $this->getContainer() : null;
        if ($container) {
            $task->setContainer($container);
        }

        $logger = $this instanceof LoggerAwareInterface ? $this->logger : null;
        if ($logger) {
            $task->setLogger($logger);
        }

        $task->setOptions($options);

        return $task;
    }
}
