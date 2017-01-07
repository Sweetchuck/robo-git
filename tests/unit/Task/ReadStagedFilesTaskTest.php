<?php

namespace Cheppers\Robo\Git\Tests\Unit\Task;

use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Git\Task\Helper as TaskHelper;
use Cheppers\Robo\Git\Task\ReadStagedFilesTask;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use Helper\Dummy\Process as DummyProcess;
use Robo\Robo;
use UnitTester;

class ReadStagedFilesTaskTest extends Unit
{
    /**
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass(ReadStagedFilesTask::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        DummyProcess::reset();
        TaskHelper::$fileExistsReturnValues = [];
    }

    public function testOptionsGetSet()
    {
        $options = [
            'assetJar' => new AssetJar(),
            'assetJarMapping' => [
                'a' => ['b', 'c'],
                'd' => ['e', 'f'],
            ],
            'workingDirectory' => 'g',
            'gitExecutable' => 'h',
            'commandOnly' => true,
            'paths' => [
                'i',
                '*.j',
            ],
        ];
        $task = new ReadStagedFilesTask($options);

        $this->assertEquals($options['assetJar'], $task->getAssetJar());
        $this->assertEquals($options['assetJarMapping'], $task->getAssetJarMapping());
        $this->assertEquals($options['workingDirectory'], $task->getWorkingDirectory());
        $this->assertEquals($options['gitExecutable'], $task->getGitExecutable());
        $this->assertEquals($options['commandOnly'], $task->getCommandOnly());
        $this->assertEquals($options['paths'], $task->getPaths());
    }

    public function casesRun()
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
                            'command' => 'git show :a.php',
                            'content' => 'Content of a.php',
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'command' => 'git show :c.php',
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
                            'command' => 'git show :a.php',
                            'content' => null,
                        ],
                        'c.php' => [
                            'fileName' => 'c.php',
                            'command' => 'git show :c.php',
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
     *
     * @param array $expected
     * @param array $stagedFileNames
     * @param array $options
     */
    public function testRun(array $expected, array $stagedFileNames, array $options)
    {
        $assetJar = new AssetJar();

        $container = Robo::createDefaultContainer();
        Robo::setContainer($container);

        /** @var \Cheppers\Robo\Git\Task\ReadStagedFilesTask $task */
        $task = Stub::make(
            ReadStagedFilesTask::class,
            [
                'assetJar' => $assetJar,
                'assetJarMapping' => [
                    'workingDirectory' => ['wd'],
                    'files' => ['f'],
                ],
                'processClass' => DummyProcess::class,
                'getStagedFileNames' => function () use ($stagedFileNames) {
                    return array_keys($stagedFileNames);
                },
            ]
        );
        $task->setOptions($options);

        TaskHelper::$fileExistsReturnValues = $stagedFileNames;

        $processIndex = count(DummyProcess::$instances);
        foreach ($expected['files'] as $file) {
            DummyProcess::$prophecy[$processIndex++] = [
                'exitCode' => 0,
                'stdOutput' => $file['content'],
                'stdError' => '',
            ];
        }

        $result = $task->run();
        $this->assertEquals($expected['workingDirectory'], $result['workingDirectory']);
        $this->assertEquals($expected['files'], $result['files']);

        $this->assertEquals($expected['workingDirectory'], $task->getAssetJarValue('workingDirectory'));
        $this->assertEquals($expected['files'], $task->getAssetJarValue('files'));
    }

    public function testGetStagedFileNames()
    {
        /** @var \Cheppers\Robo\Git\Task\ReadStagedFilesTask $task */
        $task = Stub::make(
            ReadStagedFilesTask::class,
            [
                'processClass' => DummyProcess::class,
            ]
        );
        $method = static::getMethod('getStagedFileNames');

        $processIndex = count(DummyProcess::$instances);
        DummyProcess::$prophecy[$processIndex] = [
            'exitCode' => 0,
            'stdOutput' => "a.php\nb.php",
            'stdError' => '',
        ];
        DummyProcess::$prophecy[++$processIndex] = [
            'exitCode' => 1,
            'stdOutput' => '',
            'stdError' => '',
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
