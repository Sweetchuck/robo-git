<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Sweetchuck\Robo\Git\Test\Helper\Dummy\Process as DummyProcess;
use Robo\Robo;

class GitReadStagedFilesTaskTest extends Unit
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
                            'command' => "git show :'a.php'",
                            'content' => 'Content of a.php',
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'command' => "git show :'c.php'",
                            'content' => 'Content of c.php',
                        ],
                    ],
                    'exitCode' => 0,
                ],
                [
                    'a.php' => true,
                    'b.php' => false,
                    'c.php' => true,
                ],
                [],
            ],
            'without content' => [
                [
                    'workingDirectory' => '',
                    'files' => [
                        'a.php' => [
                            'fileName' => 'a.php',
                            'command' => "git show :'a.php'",
                            'content' => null,
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'command' => "git show :'c.php'",
                            'content' => null,
                        ],
                    ],
                    'exitCode' => 0,
                ],
                [
                    'a.php' => true,
                    'b.php' => false,
                    'c.php' => true,
                ],
                [
                    'commandOnly' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(array $expected, array $stagedFileNames, array $options): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        /** @var \Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask $task */
        $task = Stub::make(
            GitReadStagedFilesTask::class,
            [
                'processClass' => DummyProcess::class,
                'getStagedFileNames' => function () use ($stagedFileNames) {
                    return array_keys($stagedFileNames);
                },
                'fileExists' => function (string $fileName) use ($stagedFileNames, $options) {
                    $wd = $options['workingDirectory'] ?? '.';
                    $key = substr($fileName, strlen($wd) + 1);

                    return $stagedFileNames[$key];
                },
            ]
        );
        $task->setOptions($options);

        $processIndex = count(DummyProcess::$instances);
        foreach ($expected['files'] as $file) {
            DummyProcess::$prophecy[$processIndex++] = [
                'exitCode' => 0,
                DummyProcess::OUT => $file['content'],
                DummyProcess::ERR => '',
            ];
        }

        $result = $task->run();

        $this->assertEquals(
            $expected['workingDirectory'],
            $result['workingDirectory'],
            'Result "workingDirectory"'
        );

        $this->assertEquals(
            $expected['files'],
            $result['files'],
            'Result "files"'
        );
    }

    public function testGetStagedFileNames(): void
    {
        /** @var \Sweetchuck\Robo\Git\Task\GitReadStagedFilesTask $task */
        $task = Stub::make(
            GitReadStagedFilesTask::class,
            [
                'processClass' => DummyProcess::class,
            ]
        );
        $method = static::getMethod('getStagedFileNames');

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            DummyProcess::OUT => "a.php\nb.php",
            DummyProcess::ERR => '',
        ];
        DummyProcess::$prophecy[++$processIndex] = [
            'exitCode' => 1,
            DummyProcess::OUT => '',
            DummyProcess::ERR => '',
        ];

        $task->setPaths(['*.php']);

        $this->tester->assertEquals(
            [
                'a.php',
                'b.php',
            ],
            $method->invokeArgs($task, [])
        );

        $this->tester->assertEquals(
            "git diff --name-only --cached -- *'.php'",
            DummyProcess::$instances[0]->getCommandLine()
        );

        try {
            $method->invokeArgs($task, []);
            $this->tester->fail('Non-zero exit code was not handled.');
        } catch (\Exception $e) {
            $this->tester->assertTrue(true, 'OK');
        }
    }
}
