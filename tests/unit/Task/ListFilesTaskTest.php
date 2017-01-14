<?php

namespace Cheppers\Robo\Git\Tests\Unit\Task;

use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Git\ListFilesItem;
use Cheppers\Robo\Git\Task\ListFilesTask;
use Codeception\Util\Stub;
use Helper\Dummy\Output as DummyOutput;
use Helper\Dummy\Process as DummyProcess;
use Robo\Robo;

class ListFilesTaskTest extends \PHPUnit_Framework_TestCase
{
    protected static function getMethod(string $name): \ReflectionMethod
    {
        $class = new \ReflectionClass(ListFilesTask::class);
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
        $task = new ListFilesTask($options);
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
        /** @var ListFilesTask $task */
        $task = Stub::make(
            ListFilesTask::class,
            [
                'processClass' => DummyProcess::class,
            ]
        );
        $method = static::getMethod('parseStdOutput');

        $task->setOptions($options);

        $this->assertEquals($expected, $method->invoke($task, $stdOutput));
    }

    public function casesRun(): array
    {
        return [
            'basic' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                    ]),
                ],
                [],
                implode("\n", [
                    'a.php',
                    '',
                ]),
            ],
            'asset jar' => [
                [
                    'a.php' => new ListFilesItem([
                        'fileName' => 'a.php',
                    ]),
                ],
                [
                    'assetJar' => new AssetJar(),
                ],
                implode("\n", [
                    'a.php',
                    '',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(array $expectedFiles, array $options, string $prophecyStdOutput): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput();

        $options += [
            'assetJarMapping' => [
                'files' => ['myTask01', 'files'],
            ],
        ];

        /** @var ListFilesTask $task */
        $task = Stub::construct(
            ListFilesTask::class,
            [$options, []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            'stdOutput' => $prophecyStdOutput,
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $result = $task->run();

        static::assertEquals(
            0,
            $result->getExitCode(),
            'Exit code is different than the expected.'
        );

        static::assertEquals(count($expectedFiles), count($result['files']));
        foreach ($expectedFiles as $fileName => $file) {
            static::assertEquals($file, $result['files'][$fileName]);
        }

        /** @var \Cheppers\AssetJar\AssetJarInterface $assetJar */
        $assetJar = !empty($options['assetJar']) ? $options['assetJar'] : null;
        if ($assetJar) {
            static::assertEquals(
                $result['files'],
                $assetJar->getValue($options['assetJarMapping']['files'])
            );
        }
    }

    public function testRunError(): void
    {
        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        $mainStdOutput = new DummyOutput();

        /** @var ListFilesTask $task */
        $task = Stub::construct(
            ListFilesTask::class,
            [[], []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 1,
            'stdOutput' => 'My custom std-output.',
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

        $mainStdOutput = new DummyOutput();

        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
        $task = Stub::construct(
            ListFilesTask::class,
            [['visibleStdOutput' => true], []],
            [
                'processClass' => DummyProcess::class,
            ]
        );

        $processIndex = count(DummyProcess::$instances);

        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            'stdOutput' => 'My custom std-output.',
        ];

        $task->setLogger($container->get('logger'));
        $task->setOutput($mainStdOutput);

        $task->run();
        $this->assertEquals('My custom std-output.', $mainStdOutput->output);
    }
}
