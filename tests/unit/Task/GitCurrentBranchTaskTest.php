<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

class GitCurrentBranchTaskTest extends TaskTestBase
{
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
        $task = $this->taskBuilder->taskGitCurrentBranch($options);

        $this->tester->assertEquals($expected, $task->getCommand());
    }
}
