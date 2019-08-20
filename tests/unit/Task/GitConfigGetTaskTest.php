<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitConfigGetTask<extended>
 */
class GitConfigGetTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                "git config 'user.name'",
                [
                    'name' => 'user.name',
                ],
            ],
            'source local' => [
                "git config --local 'user.name'",
                [
                    'name' => 'user.name',
                    'source' => 'local',
                ],
            ],
            'source system' => [
                "git config --system 'user.name'",
                [
                    'name' => 'user.name',
                    'source' => 'system',
                ],
            ],
            'source global' => [
                "git config --global 'user.name'",
                [
                    'name' => 'user.name',
                    'source' => 'global',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $this->tester->assertEquals(
            $expected,
            $this
                ->taskBuilder
                ->taskGitConfigGet($options)
                ->getCommand()
        );
    }

    public function casesRunSuccess(): array
    {
        return [
            'basic' => [
                [
                    'exitCode' => 0,
                    'assets' => [
                        'git.config.user.name' => 'Foo Bar',
                    ],
                ],
                [
                    'name' => 'user.name',
                ],
                [
                    'stdOutput' => "Foo Bar\n",
                    'stdError' => '',
                    'exitCode' => 0,
                ],
            ],
            'error with stopOnFail true' => [
                [
                    'exitCode' => 1,
                    'assets' => [
                        'git.config.user.name' => null,
                    ],
                ],
                [
                    'name' => 'user.name',
                    'stopOnFail' => true,
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 1,
                ],
            ],
            'error with stopOnFail false' => [
                [
                    'exitCode' => 0,
                    'assets' => [
                        'git.config.user.name' => null,
                    ],
                ],
                [
                    'name' => 'user.name',
                    'stopOnFail' => false,
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expected, array $options, array $prophecy): void
    {
        $expected += [
            'assets' => [],
        ];

        $prophecy += [
            'stdOutput' => '',
            'stdError' => '',
            'exitCode' => 0,
        ];

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = $prophecy;

        $result = $this
            ->taskBuilder
            ->taskGitConfigGet($options)
            ->run();

        $this->tester->assertSameSize(
            DummyProcess::$instances,
            DummyProcess::$prophecy,
            'Amount of process'
        );

        if (array_key_exists('exitCode', $expected)) {
            $this->tester->assertSame(
                $expected['exitCode'],
                $result->getExitCode(),
                'Exit code is different than the expected.'
            );
        }

        foreach ($expected['assets'] as $name => $value) {
            $this->tester->assertSame(
                $value,
                $result[$name],
                "Asset $name"
            );
        }
    }
}
