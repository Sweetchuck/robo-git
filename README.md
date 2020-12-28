
# Robo task wrapper for Git commands

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-git/tree/1.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-git/tree/1.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-git/branch/1.x/graph/badge.svg)](https://codecov.io/gh/Sweetchuck/robo-git)

The main additional feature compare to the ::taskExec() is that this tasks parse
the stdOutput/errorOutput and make the result available for the next tasks in
the `\Robo\State\Data` instance which belongs to the pipeline.


## Install

Run `composer require --dev sweetchuck/robo-git`


## Task - taskGitBranchList()

```php
<?php

declare(strict_types = 1);

class RoboFileExample extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Git\GitTaskLoader;

    public function gitBranches(string $dir = '.')
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskGitBranchList()
                    ->setWorkingDirectory($dir)
                    // Available options:
                    // --all
                    // --color
                    // --contains
                    // --format
                    // --list
                    // --merged
                    // --points-at
                    // --sort
            )
            ->addCode(function (\Robo\State\Data $data): int {
                // Here you can do whatever you want with the branches.
                $output = $this->output();
                foreach ($data['gitBranches'] as $longName => $branch) {
                    $output->writeln($longName);
                    foreach ($branch as $key => $value) {
                        $output->writeln("    $key = " . var_export($value, true));
                    }
                }

                return 0;
            });
    }
}
```

Run: `$ vendor/bin/robo git:branches`

Example output:
> <pre>refs/heads/1.x
>     isCurrentBranch = true
>     push = 'refs/remotes/upstream/1.x'
>     push.short = 'upstream/1.x'
>     refName = 'refs/heads/1.x'
>     refName.short = '1.x'
>     track = ''
>     track.ahead = NULL
>     track.behind = NULL
>     track.gone = false
>     upstream = 'refs/remotes/upstream/1.x'
>     upstream.short = 'upstream/1.x'</pre>


## Task - taskGitConfigGet()

```php
<?php

declare(strict_types = 1);

class RoboFileExample extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Git\GitTaskLoader;

    /**
     * @command git:config:get
     */
    public function gitConfigGet(string $name)
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskGitConfigGet()
                    ->setName($name)
            )
            ->addCode(function (\Robo\State\Data $data) use ($name): int {
                $key = "git.config.$name";
                $this->output()->writeln("$key = {$data[$key]}");

                return 0;
            });
    }
}
```

Run: `$ vendor/bin/robo git:config:get`

Example output:
> `git.config.user.name = Andor`

## Task - taskGitCurrentBranch()

@todo


## Task - taskGitListFiles()

@todo


## Task - taskGitListChangedFiles()

@todo


## Task - taskGitListStagedFiles()

@todo


## Task - taskGitNumOfCommitsBetween()

@todo


## Task - taskGitReadStagedFiles()

@todo


## Task - taskGitRemoteList()

@todo


## Task - taskGitStatus()

@todo


## Task - taskGitTagList()

@todo


## Task - taskGitTopLevel()

@todo
