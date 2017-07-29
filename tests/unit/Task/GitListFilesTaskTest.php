<?php

namespace Sweetchuck\Robo\Git\Tests\Unit\Task;

use Sweetchuck\Robo\Git\ListFilesItem;
use Sweetchuck\Robo\Git\Task\GitListFilesTask;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Sweetchuck\Robo\Git\Test\Helper\Dummy\Output as DummyOutput;
use Sweetchuck\Robo\Git\Test\Helper\Dummy\Process as DummyProcess;
use Robo\Robo;

class GitListFilesTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    protected static function getMethod(string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass(GitListFilesTask::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        DummyProcess::reset();

        parent::setUp();
    }

    public function casesGetCommand(): array
    {
        return [
            'basic' => [
                'git ls-files',
                []
            ],
            'working directory' => [
                "cd '\$HOME/repo-root' && git ls-files",
                [
                    'workingDirectory' => '$HOME/repo-root',
                ],
            ],
            'separatedWithNullChar' => [
                'git ls-files -z',
                [
                    'separatedWithNullChar' => true,
                ],
            ],
            'fileStatusWithTags' => [
                'git ls-files -t',
                [
                    'fileStatusWithTags' => true,
                ],
            ],
            'lowercaseStatusLetters' => [
                'git ls-files -v',
                [
                    'lowercaseStatusLetters' => true,
                ],
            ],
            'showCached' => [
                'git ls-files --cached',
                [
                    'showCached' => true,
                ],
            ],
            'showDeleted' => [
                'git ls-files --deleted',
                [
                    'showDeleted' => true,
                ],
            ],
            'showModified' => [
                'git ls-files --modified',
                [
                    'showModified' => true,
                ],
            ],
            'showOthers' => [
                'git ls-files --others',
                [
                    'showOthers' => true,
                ],
            ],
            'showIgnored' => [
                'git ls-files --ignored',
                [
                    'showIgnored' => true,
                ],
            ],
            'showStaged' => [
                'git ls-files --stage',
                [
                    'showStaged' => true,
                ],
            ],
            'showKilled' => [
                'git ls-files --killed',
                [
                    'showKilled' => true,
                ],
            ],
            'showOtherDirectoriesNamesOnly' => [
                'git ls-files --directory',
                [
                    'showOtherDirectoriesNamesOnly' => true,
                ],
            ],
            'showLineEndings' => [
                'git ls-files --eol',
                [
                    'showLineEndings' => true,
                ],
            ],
            'showEmptyDirectories' => [
                'git ls-files --empty-directory',
                [
                    'showEmptyDirectories' => true,
                ],
            ],
            'showUnmerged' => [
                'git ls-files --unmerged',
                [
                    'showUnmerged' => true,
                ],
            ],
            'showResolveUndo' => [
                'git ls-files --resolve-undo',
                [
                    'showResolveUndo' => true,
                ],
            ],
            'excludePattern' => [
                "git ls-files --exclude 'foo-*.php'",
                [
                    'excludePattern' => 'foo-*.php',
                ],
            ],
            'excludeFile' => [
                "git ls-files --exclude-file 'foo.txt'",
                [
                    'excludeFile' => 'foo.txt',
                ],
            ],
            'fullName' => [
                'git ls-files --full-name',
                [
                    'fullName' => true,
                ],
            ],
            'paths - vector' => [
                "git ls-files -- 'a.php' 'b.php'",
                [
                    'paths' => ['a.php', 'b.php'],
                ],
            ],
            'paths - on/off' => [
                "git ls-files -- 'a.php' 'c.php'",
                [
                    'paths' => ['a.php' => true, 'b.php' => false, 'c.php' => true],
                ],
            ],
            'all in one' => [
                "cd '\$HOME/repo-root' && my-git ls-files -- 'a.php'",
                [
                    'workingDirectory' => '$HOME/repo-root',
                    'gitExecutable' => 'my-git',
                    'paths' => ['a.php'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesGetCommand
     */
    public function testGetCommand(string $expected, array $options): void
    {
        $task = new GitListFilesTask($options);
        $this->assertEquals($expected, $task->getCommand());
    }

    public function casesParseStdOutput(): array
    {
        return [
            'empty' => [
                [],
                [],
                '',
            ],
            'minimal - \n' => [
                [
                    'a.php' => new ListFilesItem(['fileName' => 'a.php']),
                    'b.php' => new ListFilesItem(['fileName' => 'b.php']),
                    'c.php' => new ListFilesItem(['fileName' => 'c.php']),
                ],
                [],
                implode("\n", [
                    'a.php',
                    'b.php',
                    'c.php',
                    '',
                ]),
            ],
            'minimal - \0' => [
                [
                    'a.php' => new ListFilesItem(['fileName' => 'a.php']),
                    'b.php' => new ListFilesItem(['fileName' => 'b.php']),
                    'c.php' => new ListFilesItem(['fileName' => 'c.php']),
                ],
                [
                    'separatedWithNullChar' => true,
                ],
                implode("\0", [
                    'a.php',
                    'b.php',
                    'c.php',
                    '',
                ]),
            ],
            'showStaged' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'mask' => intval('100644', 8),
                        'objectName' => 'a000000',
                        'unknown' => '0',
                    ]),
                ],
                [
                    'showStaged' => true,
                ],
                implode("\0", [
                    '100644 a000000 0 a.php',
                ]),
            ],
            'fileStatusWithTags' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'status' => 'H',
                    ]),
                ],
                [
                    'fileStatusWithTags' => true,
                ],
                implode("\0", [
                    'H a.php',
                ]),
            ],
            'showLineEndings' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                        'eolInfoI' => 'i/lf',
                        'eolInfoW' => 'w/lf',
                        'eolAttr' => 'attr/',
                    ]),
                ],
                [
                    'showLineEndings' => true,
                ],
                implode("\0", [
                    'i/lf w/lf attr/ a.php',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider casesParseStdOutput
     */
    public function testParseStdOutput(array $expected, array $options, string $stdOutput): void
    {
        /** @var GitListFilesTask $task */
        $task = Stub::make(
            GitListFilesTask::class,
            [
                'processClass' => DummyProcess::class,
            ]
        );
        $method = static::getMethod('parseStdOutput');

        $task->setOptions($options);

        $this->assertEquals($expected, $method->invoke($task, $stdOutput));
    }

    public function casesRunSuccess(): array
    {
        return [
            'basic' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                    ]),
                ],
                [
                    'assetNamePrefix' => 'b.c.'
                ],
                implode("\n", [
                    'a.php',
                    '',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider casesRunSuccess
     */
    public function testRunSuccess(array $expectedFiles, array $options, string $prophecyStdOutput): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput([]);

        /** @var GitListFilesTask $task */
        $task = Stub::construct(
            GitListFilesTask::class,
            [$options, []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            DummyProcess::OUT => $prophecyStdOutput,
            DummyProcess::ERR => '',
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();

        static::assertEquals(
            0,
            $result->getExitCode(),
            'Exit code is different than the expected.'
        );

        $assetNamePrefix = $options['assetNamePrefix'] ?? '';

        static::assertArrayHasKey(
            "{$assetNamePrefix}workingDirectory",
            $result,
            "Asset exists: 'workingDirectory'"
        );
        static::assertArrayHasKey(
            "{$assetNamePrefix}files",
            $result,
            "Asset exists: 'files'"
        );

        static::assertEquals(count($expectedFiles), count($result["{$assetNamePrefix}files"]));
        foreach ($expectedFiles as $fileName => $file) {
            static::assertEquals($file, $result["{$assetNamePrefix}files"][$fileName]);
        }
    }

    public function testRunError(): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput([]);

        /** @var GitListFilesTask $task */
        $task = Stub::construct(
            GitListFilesTask::class,
            [[], []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 1,
            DummyProcess::OUT => 'My custom std-output.',
            DummyProcess::ERR => '',
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();
        $this->assertEquals(1, $result->getExitCode());
    }

    public function testRunVisibleStdOutput(): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput([]);

        /** @var \Sweetchuck\Robo\Git\Task\GitListFilesTask $task */
        $task = Stub::construct(
            GitListFilesTask::class,
            [['visibleStdOutput' => true], []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            DummyProcess::OUT => 'My custom std-output.',
            DummyProcess::ERR => '',
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $task->run();
        $this->assertEquals('My custom std-output.', $mainStdOutput->output);
    }
}
