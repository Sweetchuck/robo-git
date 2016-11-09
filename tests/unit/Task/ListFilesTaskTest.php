<?php

// @codingStandardsIgnoreStart
use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Git\ListFilesItem;
use Cheppers\Robo\Git\Task\ListFilesTask;
use Codeception\Util\Stub;
use Helper\Dummy\Process as DummyProcess;

class ListFilesTaskTest extends \PHPUnit_Framework_TestCase
{
    // @codingStandardsIgnoreEnd

    /**
     * @param string $name
     *
     * @return ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass(ListFilesTask::class);
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

    /**
     * @return array
     */
    public function casesGetCommand()
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
     *
     * @param string $expected
     * @param array $options
     */
    public function testGetCommand($expected, array $options)
    {
        $task = new ListFilesTask($options);
        $this->assertEquals($expected, $task->getCommand());
    }

    /**
     * @return array
     */
    public function casesParseStdOutput()
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
    public function testParseStdOutput(array $expected, array $options, $stdOutput)
    {
        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
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

    /**
     * @return array
     */
    public function casesRun()
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
     * @param array $expectedFiles
     * @param array $options
     * @param string $prophecyStdOutput
     *
     * @dataProvider casesRun
     */
    public function testRun(array $expectedFiles, array $options, $prophecyStdOutput)
    {
        $container = \Robo\Robo::createDefaultContainer();
        \Robo\Robo::setContainer($container);

        $mainStdOutput = new \Helper\Dummy\Output();

        $options += [
            'assetJarMapping' => [
                'files' => ['myTask01', 'files'],
            ],
        ];

        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
        $task = Stub::construct(
            \Cheppers\Robo\Git\Task\ListFilesTask::class,
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

    public function testRunError()
    {
        $container = \Robo\Robo::createDefaultContainer();
        \Robo\Robo::setContainer($container);

        $mainStdOutput = new \Helper\Dummy\Output();

        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
        $task = Stub::construct(
            \Cheppers\Robo\Git\Task\ListFilesTask::class,
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

    public function testRunVisibleStdOutput()
    {
        $container = \Robo\Robo::createDefaultContainer();
        \Robo\Robo::setContainer($container);

        $mainStdOutput = new \Helper\Dummy\Output();

        /** @var \Cheppers\Robo\Git\Task\ListFilesTask $task */
        $task = Stub::construct(
            \Cheppers\Robo\Git\Task\ListFilesTask::class,
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
