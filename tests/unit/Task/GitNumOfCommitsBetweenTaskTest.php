<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Robo\Git\Task\BaseTask;
use Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask;

#[CoversClass(GitNumOfCommitsBetweenTask::class)]
#[CoversClass(BaseTask::class)]
class GitNumOfCommitsBetweenTaskTest extends TaskTestBase
{
    public static function casesGetCommand(): array
    {
        return [
            'basic' => [
                "git rev-list --count 'abcdefg..HEAD'",
                [
                    'fromRevName' => 'abcdefg',
                ],
            ],
            'workingDirectory' => [
                "cd 'foo' && git rev-list --count 'abcdefg..HEAD'",
                [
                    'workingDirectory' => 'foo',
                    'fromRevName' => 'abcdefg',
                ],
            ],
            'gitExecutable' => [
                "my-git rev-list --count 'abcdefg..HEAD'",
                [
                    'gitExecutable' => 'my-git',
                    'fromRevName' => 'abcdefg',
                ],
            ],
        ];
    }

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitNumOfCommitsBetween();
        $task->setOptions($options);

        $this->tester->assertEquals($expected, $task->getCommand());
    }
}
