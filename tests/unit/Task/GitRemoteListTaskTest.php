<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitRemoteListTask
 * @covers \Sweetchuck\Robo\Git\Task\BaseTask
 */
class GitRemoteListTaskTest extends TaskTestBase
{

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'git remote --verbose',
                [],
            ],
            'workingDirectory' => [
                "cd 'foo' && git remote --verbose",
                [
                    'workingDirectory' => 'foo',
                ],
            ],
        ];
    }

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitRemoteList($options);
        $this->tester->assertSame($expected, $task->getCommand());
    }

    public function casesRunSuccess(): array
    {
        return [
            'empty' => [
                [
                    'assets' => [
                        'git.remotes' => [],
                        'git.remotes.names' => [],
                        'git.remotes.fetch' => [],
                        'git.remotes.push' => [],
                    ],
                ],
                [],
            ],
            'basic' => [
                [
                    'assets' => [
                        'git.remotes' => [
                            'a' => [
                                'fetch' => 'b',
                                'push' => 'c',
                            ],
                            'd' => [
                                'fetch' => 'e',
                                'push' => 'f',
                            ],
                        ],
                        'git.remotes.names' => ['a', 'd'],
                        'git.remotes.fetch' => [
                            'a' => 'b',
                            'd' => 'e',
                        ],
                        'git.remotes.push' => [
                            'a' => 'c',
                            'd' => 'f',
                        ],
                    ],
                ],
                [
                    'stdOutput' => implode(PHP_EOL, [
                        'a b (fetch)',
                        'a c (push)',
                        'd e (fetch)',
                        'd f (push)',
                        '',
                    ]),
                ],
            ],
        ];
    }

    #[DataProvider('casesRunSuccess')]
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
            ->taskGitRemoteList($options)
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
