<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Codeception\Attribute\DataProvider;

/**
 * @covers \Sweetchuck\Robo\Git\Task\GitTagListTask
 * @covers \Sweetchuck\Robo\Git\Task\BaseTask
 */
class GitTagListTaskTest extends TaskTestBase
{
    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                "git tag --format 'none'",
                [
                    'format' => 'none',
                ],
            ],
            'workingDirectory' => [
                "cd 'foo' && git tag --format 'none'",
                [
                    'workingDirectory' => 'foo',
                    'format' => 'none',
                ],
            ],
            'gitExecutable' => [
                "my-git tag --format 'none'",
                [
                    'gitExecutable' => 'my-git',
                    'format' => 'none',
                ],
            ],
            'merged true empty' => [
                "git tag --format 'none' --merged",
                [
                    'format' => 'none',
                    'mergedState' => true,
                ],
            ],
            'merged true foo' => [
                "git tag --format 'none' --merged 'foo'",
                [
                    'format' => 'none',
                    'mergedState' => true,
                    'mergedValue' => 'foo',
                ],
            ],
            'merged false empty' => [
                "git tag --format 'none' --no-merged",
                [
                    'format' => 'none',
                    'mergedState' => false,
                ],
            ],
            'merged false foo' => [
                "git tag --format 'none' --no-merged 'foo'",
                [
                    'format' => 'none',
                    'mergedState' => false,
                    'mergedValue' => 'foo',
                ],
            ],
            'sort' => [
                "git tag --format 'none' --sort 'foo'",
                [
                    'format' => 'none',
                    'sort' => 'foo',
                ],
            ],
            'list vector' => [
                "git tag --format 'none' --list 'a' 'b'",
                [
                    'format' => 'none',
                    'listPatterns' => ['a', 'b'],
                ],
            ],
            'list assoc' => [
                "git tag --format 'none' --list 'a' 'c'",
                [
                    'format' => 'none',
                    'listPatterns' => ['a' => true, 'b' => false, 'c' => true],
                ],
            ],
            'contains true empty' => [
                "git tag --contains --format 'none'",
                [
                    'containsState' => true,
                    'format' => 'none',
                ],
            ],
            'contains true foo' => [
                "git tag --contains 'foo' --format 'none'",
                [
                    'containsState' => true,
                    'containsValue' => 'foo',
                    'format' => 'none',
                ],
            ],
            'contains false empty' => [
                "git tag --no-contains --format 'none'",
                [
                    'containsState' => false,
                    'format' => 'none',
                ],
            ],
            'contains false foo' => [
                "git tag --no-contains 'foo' --format 'none'",
                [
                    'containsState' => false,
                    'containsValue' => 'foo',
                    'format' => 'none',
                ],
            ],
            'pointsAt foo' => [
                "git tag --format 'none' --points-at 'foo'",
                [
                    'format' => 'none',
                    'pointsAt' => 'foo',
                ],
            ],
        ];
    }

    #[DataProvider('casesGetCommand')]
    public function testGetCommand(string $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitTagList($options);

        $this->tester->assertSame($expected, $task->getCommand());
    }

    public function testGetSetListPatterns(): void
    {
        $options = [
            'listPatterns' => [],
        ];
        $task = $this->taskBuilder->taskGitTagList($options);

        $task->addListPatterns(['a']);
        $task->addListPatterns(['b' => true]);
        $this->tester->assertEquals(['a' => true, 'b' => true], $task->getListPatterns());

        $task->addListPattern('c');
        $this->tester->assertEquals(['a' => true, 'b' => true, 'c' => true], $task->getListPatterns());

        $task->removeListPatterns(['a', 'c']);
        $this->tester->assertEquals(['a' => false, 'b' => true, 'c' => false], $task->getListPatterns());

        $task->removeListPattern('b');
        $this->tester->assertEquals(['a' => false, 'b' => false, 'c' => false], $task->getListPatterns());
    }
}
