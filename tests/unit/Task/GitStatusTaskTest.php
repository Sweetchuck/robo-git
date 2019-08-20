<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitStatusTask<extended>
 */
class GitStatusTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'git status --porcelain -z',
                [],
            ],
            'renames null' => [
                'git status --porcelain -z',
                [
                    'renames' => null,
                ],
            ],
            'renames true' => [
                'git status --porcelain -z --renames',
                [
                    'renames' => true,
                ],
            ],
            'renames false' => [
                'git status --porcelain -z --no-renames',
                [
                    'renames' => false,
                ],
            ],
            'findRenames null' => [
                'git status --porcelain -z',
                [
                    'findRenames' => null,
                ],
            ],
            'findRenames 0' => [
                "git status --porcelain -z --find-renames",
                [
                    'findRenames' => 0,
                ],
            ],
            'findRenames 1' => [
                "git status --porcelain -z --find-renames '1'",
                [
                    'findRenames' => 1,
                ],
            ],
            'ignored null' => [
                "git status --porcelain -z",
                [
                    'ignored' => null,
                ],
            ],
            'ignored empty string' => [
                "git status --porcelain -z --ignored",
                [
                    'ignored' => '',
                ],
            ],
            'ignored traditional' => [
                "git status --porcelain -z --ignored 'traditional'",
                [
                    'ignored' => 'traditional',
                ],
            ],
            'untracked-files null' => [
                "git status --porcelain -z",
                [
                    'untrackedFiles' => null,
                ],
            ],
            'untracked-files empty string' => [
                "git status --porcelain -z --untracked-files",
                [
                    'untrackedFiles' => '',
                ],
            ],
            'untracked-files value' => [
                "git status --porcelain -z --untracked-files 'value'",
                [
                    'untrackedFiles' => 'value',
                ],
            ],
            'paths' => [
                "git status --porcelain -z -- '*.yml'",
                [
                    'paths' => [
                        '*.yml' => true,
                    ],
                ],
            ],
            'all-in-one' => [
                "git status --porcelain -z --renames --find-renames '1' --ignored 'a' --untracked-files 'b' -- '*.yml'",
                [
                    'renames' => true,
                    'findRenames' => 1,
                    'ignored' => 'a',
                    'untrackedFiles' => 'b',
                    'paths' => [
                        '*.yml' => true,
                        '*.php' => false,
                    ],
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
                ->taskGitStatus($options)
                ->getCommand()
        );
    }
}
