<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Test\Helper\Dummy;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Common\TaskIO;
use Robo\Contract\BuilderAwareInterface;
use Robo\State\StateAwareTrait;
use Robo\TaskAccessor;
use Sweetchuck\Robo\Git\GitComboTaskLoader;
use Sweetchuck\Robo\Git\GitTaskLoader;

class DummyTaskBuilder implements BuilderAwareInterface, ContainerAwareInterface
{
    use TaskAccessor;
    use ContainerAwareTrait;
    use StateAwareTrait;
    use TaskIO;
    use GitTaskLoader {
        taskGitBranchList as public;
        taskGitCurrentBranch as public;
        taskGitListFiles as public;
        taskGitListChangedFiles as public;
        taskGitListStagedFiles as public;
        taskGitNumOfCommitsBetween as public;
        taskGitReadStagedFiles as public;
        taskGitRemoteList as public;
        taskGitTagList as public;
        taskGitTopLevel as public;
    }

    use GitComboTaskLoader {
        taskGitCloneAndClean as public;
    }

    public function collectionBuilder(): CollectionBuilder
    {
        return CollectionBuilder::create($this->getContainer(), null);
    }
}
