<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Sweetchuck\Robo\Git\Task\BaseTask;
use Sweetchuck\Robo\Git\Task\GitTopLevelTask;

#[CoversClass(GitTopLevelTask::class)]
#[CoversClass(BaseTask::class)]
class GitTopLevelTaskTest extends TaskTestBase
{
    public static function casesGetCommand(): array
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

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, ?array $options = null): void
    {
        $task = $this->taskBuilder->taskGitTopLevel();
        if ($options !== null) {
            $task->setOptions($options);
        }

        $this->tester->assertEquals($expected, $task->getCommand());
    }

    public static function casesRunSuccess(): array
    {
        return [
            'empty' => [
                [
                    'assets' => [
                        'git.topLevel' => '',
                    ],
                ],
            ],
            'something' => [
                [
                    'assets' => [
                        'git.topLevel' => '/a/b/c',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('casesRunSuccess')]
    public function testRunSuccess(array $expected, array $options = []): void
    {

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';

        DummyProcess::$prophecy[] = [
            'exitCode' => 0,
            'stdOutput' => $expected['assets']["{$assetNamePrefix}git.topLevel"] . "\n",
            'stdError' => '',
        ];

        $result = $this
            ->taskBuilder
            ->taskGitTopLevel($options)
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
