<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitCurrentBranchTask
 * @covers \Sweetchuck\Robo\Git\Task\BaseTask
 */
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

    public function casesRunSuccess(): array
    {
        return [
            'empty' => [
                [
                    'assets' => [
                        'gitCurrentBranch.long' => '',
                        'gitCurrentBranch.short' => '',
                    ],
                ],
                [],
            ],
            'basic' => [
                [
                    'assets' => [
                        'gitCurrentBranch.long' => 'refs/heads/issue/42',
                        'gitCurrentBranch.short' => 'issue/42',
                    ],
                ],
                [
                    'stdOutput' => 'refs/heads/issue/42' . PHP_EOL,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess($expected, array $prophecy, array $options = []): void
    {
        $prophecy += [
            'exitCode' => 0,
            'stdOutput' => '',
            'stdError' => '',
        ];

        DummyProcess::$prophecy[] = $prophecy;

        $result = $this
            ->taskBuilder
            ->taskGitCurrentBranch($options)
            ->setContainer($this->container)
            ->run();

        $this->tester->assertSameSize(
            DummyProcess::$instances,
            DummyProcess::$prophecy,
            'Amount of process'
        );

        foreach ($expected['assets'] as $key => $value) {
            $this->tester->assertEquals($value, $result[$key], "Result '$key'");
        }
    }
}
