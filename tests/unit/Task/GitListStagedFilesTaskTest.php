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
        return [
            'basic' => [
                'git diff --name-only --cached',
                [],
            ],
            'paths' => [
                "git diff --name-only --cached -- '*.php'",
                [
                    'paths' => [
                        '*.php' => true,
                        '*.scss' => false,
                    ],
                ],
            ],
            'filePathStyle:relativeToWorkingDirectory' => [
                "git diff --name-only --cached --relative",
                [
                    'filePathStyle' => 'relativeToWorkingDirectory',
                ],
            ],
            'filePathStyle:absolute' => [
                "git diff --name-only --cached",
                [
                    'filePathStyle' => 'absolute',
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
