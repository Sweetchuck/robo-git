<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitBranchListTask<extended>
 */
class GitBranchListTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                "git branch --format 'none'",
                [
                    'format' => 'none',
                ],
            ],
            'workingDirectory' => [
                "cd 'foo' && git branch --format 'none'",
                [
                    'workingDirectory' => 'foo',
                    'format' => 'none',
                ],
            ],
            'gitExecutable' => [
                "my-git branch --format 'none'",
                [
                    'gitExecutable' => 'my-git',
                    'format' => 'none',
                ],
            ],
            'merged true empty' => [
                "git branch --format 'none' --merged",
                [
                    'format' => 'none',
                    'mergedState' => true,
                ],
            ],
            'merged true foo' => [
                "git branch --format 'none' --merged 'foo'",
                [
                    'format' => 'none',
                    'mergedState' => true,
                    'mergedValue' => 'foo',
                ],
            ],
            'merged false empty' => [
                "git branch --format 'none' --no-merged",
                [
                    'format' => 'none',
                    'mergedState' => false,
                ],
            ],
            'merged false foo' => [
                "git branch --format 'none' --no-merged 'foo'",
                [
                    'format' => 'none',
                    'mergedState' => false,
                    'mergedValue' => 'foo',
                ],
            ],
            'sort' => [
                "git branch --format 'none' --sort 'foo'",
                [
                    'format' => 'none',
                    'sort' => 'foo',
                ],
            ],
            'list vector' => [
                "git branch --format 'none' --list 'a' 'b'",
                [
                    'format' => 'none',
                    'listPatterns' => ['a', 'b'],
                ],
            ],
            'list assoc' => [
                "git branch --format 'none' --list 'a' 'c'",
                [
                    'format' => 'none',
                    'listPatterns' => ['a' => true, 'b' => false, 'c' => true],
                ],
            ],
            'contains true empty' => [
                "git branch --contains --format 'none'",
                [
                    'containsState' => true,
                    'containsValue' => '',
                    'format' => 'none',
                ],
            ],
            'contains false empty' => [
                "git branch --no-contains --format 'none'",
                [
                    'containsState' => false,
                    'containsValue' => '',
                    'format' => 'none',
                ],
            ],
            'contains true foo' => [
                "git branch --contains 'foo' --format 'none'",
                [
                    'containsState' => true,
                    'containsValue' => 'foo',
                    'format' => 'none',
                ],
            ],
            'contains false foo' => [
                "git branch --no-contains 'foo' --format 'none'",
                [
                    'containsState' => false,
                    'containsValue' => 'foo',
                    'format' => 'none',
                ],
            ],
            'pointsAt foo' => [
                "git branch --format 'none' --points-at 'foo'",
                [
                    'format' => 'none',
                    'pointsAt' => 'foo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitBranchList($options);

        $this->tester->assertSame($expected, $task->getCommand());
    }

    public function testGetSetListPatterns(): void
    {
        $options = [
            'listPatterns' => [],
        ];

        $task = $this->taskBuilder->taskGitBranchList($options);

        $task->addListPatterns(['a']);
        $task->addListPatterns(['b' => true]);
        $this->tester->assertSame(
            [
                'b' => true,
                'a' => true,
            ],
            $task->getListPatterns(),
            'initial state'
        );

        $task->addListPattern('c');
        $this->tester->assertSame(
            [
                'b' => true,
                'a' => true,
                'c' => true,
            ],
            $task->getListPatterns(),
            'c added'
        );

        $task->removeListPatterns(['a', 'c']);
        $this->tester->assertSame(
            [
                'a' => false,
                'c' => false,
                'b' => true,
            ],
            $task->getListPatterns(),
            'c removed'
        );

        $task->removeListPattern('b');
        $this->tester->assertSame(
            [
                'a' => false,
                'c' => false,
                'b' => false,
            ],
            $task->getListPatterns(),
            'b removed'
        );
    }
}
