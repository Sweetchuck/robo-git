<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitListStagedFilesTask;
use Codeception\Test\Unit;

class GitListStagedFilesTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

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
        $task = new GitListStagedFilesTask();
        $task->setOptions($options);
        $this->assertEquals($expected, $task->getCommand());
    }
}
