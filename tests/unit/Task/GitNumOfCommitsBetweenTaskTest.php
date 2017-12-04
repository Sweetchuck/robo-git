<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitNumOfCommitsBetweenTask;
use Codeception\Test\Unit;

class GitNumOfCommitsBetweenTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesGetCommand(): array
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

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new GitNumOfCommitsBetweenTask();
        $task->setOptions($options);
        $this->assertEquals($expected, $task->getCommand());
    }
}
