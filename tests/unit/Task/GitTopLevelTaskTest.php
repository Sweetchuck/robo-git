<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitTopLevelTask;
use Codeception\Test\Unit;

class GitTopLevelTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'git rev-parse --show-toplevel',
            ],
            'workingDirectory' => [
                "cd 'my-dir' && git rev-parse --show-toplevel",
                [
                    'workingDirectory' => 'my-dir',
                ],
            ],
            'workingDirectory; gitExecutable' => [
                "cd 'my-dir' && my-git rev-parse --show-toplevel",
                [
                    'workingDirectory' => 'my-dir',
                    'gitExecutable' => 'my-git',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, ?array $options = null): void
    {
        $task = new GitTopLevelTask();
        if ($options !== null) {
            $task->setOptions($options);
        }
        $this->assertEquals($expected, $task->getCommand());
    }
}
