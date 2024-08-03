<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\Task\BaseTask;
use Sweetchuck\Robo\Git\Task\GitListChangedFilesTask;

#[CoversClass(GitListChangedFilesTask::class)]
#[CoversClass(BaseTask::class)]
class GitListChangedFilesTaskTest extends TaskTestBase
{
    public static function casesGetCommand(): array
    {
        $cmd = 'git --no-pager diff --no-color --name-status -z';

        return [
            'basic' => [
                $cmd,
                [],
            ],
            'basic only from' => [
                "$cmd 'my-from'",
                [
                    'fromRevName' => 'my-from',
                ],
            ],
            'basic only to' => [
                "$cmd 'my-to'",
                [
                    'toRevName' => 'my-to',
                ],
            ],
            'paths' => [
                "$cmd 'master..i42' -- '*.php'",
                [
                    'fromRevName' => 'master',
                    'toRevName' => 'i42',
                    'paths' => [
                        '*.php' => true,
                        '*.scss' => false,
                    ],
                ],
            ],
            'filePathStyle:relativeToWorkingDirectory' => [
                "$cmd --relative 'master..i42'",
                [
                    'filePathStyle' => 'relativeToWorkingDirectory',
                    'fromRevName' => 'master',
                    'toRevName' => 'i42',
                ],
            ],
            'filePathStyle:absolute' => [
                "$cmd 'master..i42'",
                [
                    'filePathStyle' => 'absolute',
                    'fromRevName' => 'master',
                    'toRevName' => 'i42',
                ],
            ],
            'diffFilter' => [
                "$cmd --diff-filter 'AMd' 'master..i42'",
                [
                    'diffFilter' => ['A' => false, 'a' => true, 'm' => true, 'd' => false, 'C' => null],
                    'fromRevName' => 'master',
                    'toRevName' => 'i42',
                ],
            ],
        ];
    }

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitListChangedFiles($options);

        $this->tester->assertEquals($expected, $task->getCommand());
    }
}
