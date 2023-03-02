<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitListStagedFilesTask
 * @covers \Sweetchuck\Robo\Git\Task\BaseTask
 */
class GitListStagedFilesTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        $cmd = 'git --no-pager diff --no-color --name-status --cached -z';

        return [
            'basic' => [
                $cmd,
                [],
            ],
            'paths' => [
                "$cmd -- '*.php'",
                [
                    'paths' => [
                        '*.php' => true,
                        '*.scss' => false,
                    ],
                ],
            ],
            'filePathStyle:relativeToWorkingDirectory' => [
                "$cmd --relative",
                [
                    'filePathStyle' => 'relativeToWorkingDirectory',
                ],
            ],
            'filePathStyle:absolute' => [
                $cmd,
                [
                    'filePathStyle' => 'absolute',
                ],
            ],
            'diffFilter' => [
                "$cmd --diff-filter 'AMd'",
                [
                    'diffFilter' => ['A' => false, 'a' => true, 'm' => true, 'd' => false, 'C' => null],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitListStagedFiles($options);

        $this->tester->assertEquals($expected, $task->getCommand());
    }
}
