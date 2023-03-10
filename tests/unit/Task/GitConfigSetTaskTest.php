<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitConfigSetTask
 * @covers \Sweetchuck\Robo\Git\Task\GitConfigTaskBase
 * @covers \Sweetchuck\Robo\Git\Task\BaseTask
 */
class GitConfigSetTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        return [
            'basic set' => [
                "git config 'user.name' 'my value'",
                [
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
            ],
            'basic unset' => [
                "git config --unset 'user.name'",
                [
                    'name' => 'user.name',
                    'value' => null,
                ],
            ],
            'source local' => [
                "git config --local 'user.name' 'my value'",
                [
                    'source' => 'local',
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
            ],
            'source system' => [
                "git config --system 'user.name' 'my value'",
                [
                    'source' => 'system',
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
            ],
            'source global' => [
                "git config --global 'user.name' 'my value'",
                [
                    'source' => 'global',
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
            ],
        ];
    }

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $this->tester->assertSame(
            $expected,
            $this
                ->taskBuilder
                ->taskGitConfigSet($options)
                ->getCommand(),
        );
    }

    public function casesRunSuccess(): array
    {
        return [
            'basic' => [
                [
                    'exitCode' => 0,
                ],
                [
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 0,
                ],
            ],
            'unset success' => [
                [
                    'exitCode' => 0,
                ],
                [
                    'name' => 'user.name',
                    'value' => 'my value',
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 0,
                ],
            ],
            'unset name exists 1; stop on fail 1' => [
                [
                    'exitCode' => 0,
                ],
                [
                    'name' => 'user.name',
                    'value' => null,
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 0,
                ],
            ],
            'unset name exists 0; stop on fail 1' => [
                [
                    'exitCode' => 0,
                ],
                [
                    'name' => 'user.name',
                    'value' => null,
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 5,
                ],
            ],
            'io error' => [
                [
                    'exitCode' => 4,
                ],
                [
                    'name' => 'user.name',
                    'value' => null,
                ],
                [
                    'stdOutput' => '',
                    'stdError' => '',
                    'exitCode' => 4,
                ],
            ],
        ];
    }

    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $options, array $prophecy): void
    {
        $prophecy += [
            'stdOutput' => '',
            'stdError' => '',
            'exitCode' => 0,
        ];

        DummyProcess::$prophecy[] = $prophecy;

        $result = $this
            ->taskBuilder
            ->taskGitConfigSet($options)
            ->run();

        if (array_key_exists('exitCode', $expected)) {
            $this->tester->assertSame(
                $expected['exitCode'],
                $result->getExitCode(),
                'Exit code is different than the expected.'
            );
        }
    }
}
