<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcess;
use Robo\Robo;

class GitReadStagedFilesTaskTest extends TaskTestBase
{
    protected static function getMethod(string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass(GitReadStagedFilesTask::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        DummyProcess::reset();
    }

    public function testOptionsGetSet(): void
    {
        $options = [
            'workingDirectory' => 'g',
            'gitExecutable' => 'h',
            'commandOnly' => true,
            'paths' => [
                'i' => true,
                '*.j' => true,
            ],
        ];
        $task = new GitReadStagedFilesTask();
        $task->setOptions($options);

        $this->assertEquals($options['workingDirectory'], $task->getWorkingDirectory());
        $this->assertEquals($options['gitExecutable'], $task->getGitExecutable());
        $this->assertEquals($options['commandOnly'], $task->getCommandOnly());
        $this->assertEquals($options['paths'], $task->getPaths());
    }

    public function casesRun(): array
    {
        return [
            'empty' => [
                [
                    'workingDirectory' => '',
                    'files' => [],
                    'exitCode' => 0,
                ],
                [],
                [],
            ],
            'with content' => [
                [
                    'workingDirectory' => '',
                    'files' => [
                        'a.php' => [
                            'fileName' => 'a.php',
                            'content' => 'Content of a.php',
                            'command' => "git --no-pager show :'a.php'",
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'content' => 'Content of c.php',
                            'command' => "git --no-pager show :'c.php'",
                        ],
                    ],
                    'exitCode' => 0,
                ],
                [
                    'paths' => [
                        'a.php' => true,
                        'b.php' => false,
                        'c.php' => true,
                    ],
                ],
            ],
            'without content' => [
                [
                    'workingDirectory' => '',
                    'files' => [
                        'a.php' => [
                            'fileName' => 'a.php',
                            'content' => null,
                            'command' => "git --no-pager show :'a.php'",
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'content' => null,
                            'command' => "git --no-pager show :'c.php'",
                        ],
                    ],
                    'exitCode' => 0,
                ],
                [
                    'paths' => [
                        'a.php' => true,
                        'b.php' => false,
                        'c.php' => true,
                    ],
                    'commandOnly' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(array $expected, array $options): void
    {
        $task = $this->taskBuilder->taskGitReadStagedFiles($options);
        $task->setContainer($this->container);
        $task->setOptions($options);

        foreach ($expected['files'] as $file) {
            if ($file['content'] === null) {
                continue;
            }

            DummyProcess::$prophecy[] = [
                'exitCode' => 0,
                'stdOutput' => $file['content'],
                'stdError' => '',
            ];
        }

        $result = $task->run();

        static::assertSameSize(
            DummyProcess::$instances,
            DummyProcess::$prophecy,
            'Amount of process'
        );

        static::assertSame(
            $expected['workingDirectory'],
            $result['workingDirectory'],
            'Result "workingDirectory"'
        );

        static::assertSame(
            $expected['files'],
            $result['files'],
            'Result "files"'
        );
    }
}
