<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitTagListTask;
use Codeception\Test\Unit;

class GitTagListTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'git tag',
                [],
            ],
            'workingDirectory' => [
                "cd 'foo' && git tag",
                ['workingDirectory' => 'foo'],
            ],
            'gitExecutable' => [
                'my-git tag',
                ['gitExecutable' => 'my-git'],
            ],
            'merged true empty' => [
                'git tag --merged',
                ['mergedState' => true],
            ],
            'merged true foo' => [
                "git tag --merged 'foo'",
                ['mergedState' => true, 'mergedValue' => 'foo'],
            ],
            'merged false empty' => [
                'git tag --no-merged',
                ['mergedState' => false],
            ],
            'merged false foo' => [
                "git tag --no-merged 'foo'",
                ['mergedState' => false, 'mergedValue' => 'foo'],
            ],
            'sort' => [
                "git tag --sort 'foo'",
                ['sort' => 'foo'],
            ],
            'list vector' => [
                "git tag --list 'a' 'b'",
                ['listPatterns' => ['a', 'b']],
            ],
            'list assoc' => [
                "git tag --list 'a' 'c'",
                ['listPatterns' => ['a' => true, 'b' => false, 'c' => true]],
            ],
            'contains empty' => [
                'git tag --contains',
                ['contains' => ''],
            ],
            'contains foo' => [
                "git tag --contains 'foo'",
                ['contains' => 'foo'],
            ],
            'pointsAt foo' => [
                "git tag --points-at 'foo'",
                ['pointsAt' => 'foo'],
            ],
            'format empty string' => [
                'git tag',
                ['format' => ''],
            ],
            'format empty array' => [
                'git tag',
                ['format' => []],
            ],
            'format string' => [
                "git tag --format 'foo'",
                ['format' => 'foo'],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new GitTagListTask($options);
        $this->assertEquals($expected, $task->getCommand());
    }

    public function testGetSetListPatterns(): void
    {
        $options = [
            'listPatterns' => [],
        ];
        $task = new GitTagListTask($options);
        $task->addListPatterns(['a']);
        $task->addListPatterns(['b' => true]);
        $this->assertEquals(['a' => true, 'b' => true], $task->getListPatterns());

        $task->addListPattern('c');
        $this->assertEquals(['a' => true, 'b' => true, 'c' => true], $task->getListPatterns());

        $task->removeListPatterns(['a', 'c']);
        $this->assertEquals(['a' => false, 'b' => true, 'c' => false], $task->getListPatterns());

        $task->removeListPattern('b');
        $this->assertEquals(['a' => false, 'b' => false, 'c' => false], $task->getListPatterns());
    }
}
