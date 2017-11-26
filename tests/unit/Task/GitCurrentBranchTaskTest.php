<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitCurrentBranchTask;
use Codeception\Test\Unit;

class GitCurrentBranchTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                "git symbolic-ref 'HEAD'",
                [],
            ],
            'workingDirectory' => [
                "cd 'foo' && git symbolic-ref 'HEAD'",
                ['workingDirectory' => 'foo'],
            ],
            'gitExecutable' => [
                "my-git symbolic-ref 'HEAD'",
                ['gitExecutable' => 'my-git'],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new GitCurrentBranchTask($options);
        $this->assertEquals($expected, $task->getCommand());
    }
}
