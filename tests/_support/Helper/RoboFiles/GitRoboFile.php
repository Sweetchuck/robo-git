<?php

namespace Sweetchuck\Robo\Git\Test\Helper\RoboFiles;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Common\OutputAdapter;
use Robo\State\Data as RoboStateData;
use Sweetchuck\Robo\Git\GitComboTaskLoader;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Robo\Collection\CollectionBuilder;
use Robo\Tasks as BaseRoboFile;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

class GitRoboFile extends BaseRoboFile implements LoggerAwareInterface
{
    use GitTaskLoader;
    use GitComboTaskLoader;
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $tmpDirBase = 'tests/_data/tmp';

    /**
     * {@inheritdoc}
     */
    protected function output()
    {
        return $this->getContainer()->get('output');
    }

    // region Task - GitBranchListTask
    /**
     * @command branch-list:basic
     */
    public function branchListBasic(
        array $options = [
            'all' => false,
            'mergedStatus' => null,
            'mergedValue' => '',
            'sort' => '',
        ]
    ): CollectionBuilder {
        $taskOptions = array_intersect_key(
            $options,
            [
                'all' => false,
                'mergedStatus' => null,
                'mergedValue' => '',
                'sort' => '',
            ]
        );

        return $this
            ->branchListPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitBranchList($taskOptions)
                    ->setWorkingDirectory('local')
                    ->setVisibleStdOutput(false)
            )
            ->addCode(function (RoboStateData $data) {
                $this->output()->writeln(Yaml::dump($data['gitBranches'], 50));
            });
    }

    /**
     * @return $this
     */
    protected function branchListPrepareGitRepo(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskFilesystemStack()
                    ->stopOnFail(true)
                    ->mkdir('local')
                    ->mkdir('remote')
            )
            ->addCode(function (RoboStateData $data): int {
                $data['localDir'] = getcwd() . '/local';
                $data['remoteDir'] = getcwd() . '/remote';

                $data['local/a.txt'] = "{$data['localDir']}/a.txt";

                return 0;
            })
            ->addTask(
                $this
                    ->taskGitStack()
                    ->stopOnFail(true)
                    ->printOutput(false)
                    ->exec('init --initial-branch=main --bare')
                    ->deferTaskConfiguration('dir', 'remoteDir')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('')
                    ->text("# Step 01\n")
                    ->deferTaskConfiguration('filename', 'local/a.txt')
            )
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->exec("remote add 'origin' '../remote'")
                    ->add('a.txt')
                    ->commit('Initial commit')
                    ->tag('1.0.0')
                    ->exec('branch "8.x-1.x"')
                    ->exec('push origin 8.x-1.x:8.x-1.x --set-upstream')
                    ->deferTaskConfiguration('dir', 'localDir')
            );
    }
    // endregion

    // region Task - GitCloneAndClean
    /**
     * @command clone-and-clean:success
     */
    public function cloneAndCleanExistsSuccess(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.', realpath($this->tmpDirBase))
                    ->cwd(true)
            )
            ->addCode(function (RoboStateData $data): int {
                $data['./wc'] = "{$data['path']}/wc";
                $data['./release'] = "{$data['path']}/release";

                return 0;
            })
            ->addTask(
                $this
                    ->taskExecStack()
                    ->exec('git init --initial-branch=main --bare release.git')

                    ->exec('git clone release.git release')
                    ->exec('cd release')
                    ->exec('git switch --create=main')
                    ->exec('touch a.txt')
                    ->exec('git add a.txt')
                    ->exec('git commit -m "Add a.txt"')
                    ->exec('git push origin main:main')
                    ->exec('cd ..')

                    ->exec('git init --initial-branch=main wc')
                    ->exec('cd wc')
                    ->exec('touch b.txt')
                    ->exec('git add b.txt')
                    ->exec('git commit -m "Add b.txt"')
                    ->exec('git remote add live ../release.git')
                    ->exec('git fetch live main:live/main')
                    ->exec('cd ..')

                    ->exec('cd release')
                    ->exec('echo "line 1" >> a.txt')
                    ->exec('git add a.txt')
                    ->exec('git commit -m "Modify a.txt"')
                    ->exec('git push origin main:main')
                    ->exec('cd ..')
                    ->exec('rm -rf release')
            )
            ->addTask(
                $this
                    ->taskGitCloneAndClean()
                    ->setRemoteName('release-store')
                    ->setRemoteUrl('../release.git')
                    ->setRemoteBranch('main')
                    ->setLocalBranch('main')
                    ->deferTaskConfiguration('setSrcDir', './wc')
                    ->deferTaskConfiguration('setWorkingDirectory', './release')
            )
            ->addCode(function (): int {
                $dirs = [
                    'wc' => 'wc',
                    'release' => 'release',
                ];
                $output = new BufferedOutput();
                $outputAdapter = new OutputAdapter();
                $outputAdapter->setOutput($output);

                $actual = [];
                foreach ($dirs as $name => $dir) {
                    $result = $this
                        ->taskGitBranchList()
                        ->setWorkingDirectory($dir)
                        ->setFormat([
                            'isCurrentBranch' => 'HEAD',
                            'push.short' => 'push:strip=0',
                            'upstream.short' => 'upstream:strip=0',
                        ])
                        ->run();
                    $actual[$name]['branches'] = $result['gitBranches'];

                    $result = $this
                        ->taskGitRemoteList()
                        ->setWorkingDirectory($dir)
                        ->run();
                    $actual[$name]['remotes'] = $result['git.remotes.fetch'];

                    $result = $this
                        ->taskGitStatus()
                        ->setWorkingDirectory($dir)
                        ->run();
                    $actual[$name]['status'] = $result['git.status'];
                }

                $this->output()->write(Yaml::dump($actual, 99));

                return 0;
            });
    }
    // endregion

    // region Task - GitCurrentBranchTask
    /**
     * @command current-branch:success
     */
    public function currentBranchSuccess(string $branchName): CollectionBuilder
    {
        return $this
            ->currentBranchPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitStack()
                    ->checkout($branchName)
                    ->printOutput(false)
            )
            ->addTask($this->taskGitCurrentBranch())
            ->addCode(function (RoboStateData $data) {
                $this->output()->writeln("long: {$data['gitCurrentBranch.long']}");
                $this->output()->writeln("short: {$data['gitCurrentBranch.short']}");
            });
    }

    /**
     * @return $this
     */
    protected function currentBranchPrepareGitRepo(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.current-branch.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskWriteToFile('README.md')
                    ->text("# Foo\n")
            )
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->exec('checkout -b 1.0.x')
                    ->add('README.md')
                    ->commit('Initial commit')
                    ->tag('1.0.0')
                    ->exec('branch 1.1.x')
                    ->exec('branch personal/a')
                    ->exec('branch personal/b/c')
                    ->exec('branch personal/b/d')
            );
    }
    // endregion

    // region Task - GitListFilesTask
    /**
     * @command list-files
     */
    public function listFiles(): CollectionBuilder
    {
        return $this
            ->listFilesPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitListFiles()
                    ->setVisibleStdOutput(true)
                    ->setShowStaged(true)
                    ->setFileStatusWithTags(true)
                    ->setOutput($this->output())
            );
    }

    /**
     * @param string $tmpDir
     */
    protected function listFilesPrepareGitRepo(): CollectionBuilder
    {

        $cb = $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.list-files.', $this->tmpDirBase)
                    ->cwd(true)
            );

        foreach (['a', 'b', 'c'] as $fileName) {
            $cb->addTask(
                $this
                    ->taskWriteToFile("$fileName.php")
                    ->lines([
                        '<?php',
                        '',
                        '$a = "foo";',
                    ])
            );
        }

        $cb
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->add('a.php')
                    ->add('b.php')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('b.php')
                    ->append(true)
                    ->replace('foo', 'bar')
            );

        return $cb;
    }
    // endregion

    // region Task - GitListStagedFilesTask
    /**
     * @command list-staged-files
     */
    public function listStagedFiles()
    {
        return $this
            ->listStagedFilesPrepareGitRepo()
            ->addTask($this->taskGitListStagedFiles())
            ->addCode(function (RoboStateData $data): int {
                /** @var \Sweetchuck\Robo\Git\ListStagedFilesItem $file */
                foreach ($data['files'] as $file) {
                    $this->output()->writeln(sprintf('%s - %s', $file->status, $file->fileName));
                }

                return 0;
            });
    }

    protected function listStagedFilesPrepareGitRepo(): CollectionBuilder
    {
        return $this->readStagedFilesPrepareGitRepo();
    }
    // endregion

    // region Task - GitListChangedFiles
    /**
     * @command list-changed-files
     */
    public function listChangedFiles($fromRevName = '', $toRevName = '')
    {
        $listChangedFilesTask = $this->taskGitListChangedFiles();
        if ($fromRevName) {
            $listChangedFilesTask->setFromRevName($fromRevName);
            if ($toRevName) {
                $listChangedFilesTask->setToRevName($toRevName);
            }
        }

        return $this
            ->listChangedFilesPrepareGitRepo()
            ->addTask($listChangedFilesTask)
            ->addCode(function (RoboStateData $data): int {
                $output = $this->output();
                /** @var \Sweetchuck\Robo\Git\ListStagedFilesItem $file */
                foreach ($data['files'] as $file) {
                    $output->writeln(sprintf('%s - %s', $file->status, $file->fileName));
                }

                return 0;
            });
    }

    protected function listChangedFilesPrepareGitRepo(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.list-changed-files.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskWriteToFile('a.php')
                    ->text('a-content')
            )
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->add('a.php')
                    ->commit('Initial commit')
                    ->tag('1.0.0')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('a.php')
                    ->append()
                    ->line('New line 1')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('b.php')
                    ->text('b-content')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('c.php')
                    ->text('c-content')
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->printOutput(false)
                    ->add('a.php')
                    ->add('b.php')
                    ->commit("Add new files")
                    ->tag('1.0.1')
            );
    }

    // endregion

    // region Task - GitNumOfCommitsBetweenTask
    /**
     * @command num-of-commits-between:basic
     */
    public function numOfCommitsBetweenBasic(string $fromRevName, string $toRevName): CollectionBuilder
    {
        return $this
            ->numOfCommitsBetweenPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitNumOfCommitsBetween()
                    ->setVisibleStdOutput(false)
                    ->setFromRevName($fromRevName)
                    ->setToRevName($toRevName)
                    ->setOutput($this->output())
            )
            ->addCode(function (RoboStateData $data) {
                $this->output()->writeln($data['numOfCommits']);

                return 0;
            });
    }

    protected function numOfCommitsBetweenPrepareGitRepo(): CollectionBuilder
    {
        $readMeContent = "# Foo\n";
        $readMeFileName = 'README.md';

        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.num-of-commits-between.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->text($readMeContent)
            )
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->add($readMeFileName)
                    ->commit('Initial commit')
                    ->tag('1.0.0')
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->append()
                    ->line('New line 1')
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->printOutput(false)
                    ->add($readMeFileName)
                    ->commit("Add new line 1 to '{$readMeFileName}'")
                    ->tag('1.0.1')
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->append()
                    ->line('New line 2')
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->printOutput(false)
                    ->add($readMeFileName)
                    ->commit("Add new line 2 to '{$readMeFileName}'")
                    ->tag('1.0.3')
            );
    }
    // endregion

    // region Task - GitReadStagedFilesTask
    /**
     * @command read-staged-files:with-content
     */
    public function readStagedFilesWithContent()
    {
        return $this
            ->readStagedFilesPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitListStagedFiles()
                    ->setPaths(['*.php' => true])
            )
            ->addTask(
                $this
                    ->taskGitReadStagedFiles()
                    ->deferTaskConfiguration('setPaths', 'fileNames')
            )
            ->addCode(function (RoboStateData $data) {
                $output = $this->output();
                $output->writeln('*** BEGIN Output ***');
                foreach ($data['files'] as $file) {
                    $output->writeln("--- {$file['fileName']} ---");
                    $output->write($file['content']);
                }
                $output->writeln('*** END Output ***');

                return 0;
            });
    }

    /**
     * @command read-staged-files:without-content
     */
    public function readStagedFilesWithoutContent()
    {
        return  $this
            ->readStagedFilesPrepareGitRepo()
            ->addTask(
                $this
                    ->taskGitListStagedFiles()
                    ->setPaths(['*.php' => true])
            )
            ->addTask(
                $this
                    ->taskGitReadStagedFiles()
                    ->setCommandOnly(true)
                    ->deferTaskConfiguration('setPaths', 'fileNames')
            )
            ->addCode(function (RoboStateData $data) {
                $this->output()->writeln('*** BEGIN Output ***');
                foreach ($data['files'] as $file) {
                    $this->output()->writeln("--- {$file['fileName']} ---");
                    $this->output()->writeln("{$file['command']}");
                }
                $this->output()->writeln('*** END Output ***');

                return 0;
            });
    }

    /**
     * @param string $tmpDir
     */
    protected function readStagedFilesPrepareGitRepo(): CollectionBuilder
    {
        $cb = $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.read-staged-files.', $this->tmpDirBase)
                    ->cwd(true)
            );

        // Creates 3 files with the same content.
        foreach (['a', 'b', 'c'] as $fileName) {
            $cb->addTask(
                $this
                    ->taskWriteToFile("$fileName.php")
                    ->lines([
                        '<?php',
                        '',
                        '$a = "foo";',
                    ])
            );
        }

        // Add two of them to the stage.
        $cb->addTask(
            $this
                ->getTaskGitStackInitWorkingCopy()
                ->add('a.php')
                ->add('b.php')
        );

        // Change all of them.
        // Now the staged content is different than the written one.
        foreach (['a', 'b', 'c'] as $fileName) {
            $cb->addTask(
                $this
                    ->taskWriteToFile("$fileName.php")
                    ->append(true)
                    ->replace('foo', 'bar')
            );
        }

        return $cb;
    }
    // endregion

    // region Task - GitTagListTask
    /**
     * @command tag-list:basic
     */
    public function tagListBasic(): CollectionBuilder
    {
        return $this
            ->tagListPrepareGitRepo()
            ->addTask($this->taskGitTagList())
            ->addCode(function (RoboStateData $data) {
                $tags = $data['git.tags'];
                $shaCounter = 1;
                foreach (array_keys($tags) as $tag) {
                    $tags[$tag]['objectName'] = 'SHA-' . $shaCounter++;
                }
                $this->output()->writeln(Yaml::dump($tags, 50));

                return 0;
            });
    }

    protected function tagListPrepareGitRepo(): CollectionBuilder
    {
        $readMeContent = "# Foo\n";
        $readMeFileName = 'README.md';

        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.tag-list.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->text($readMeContent)
            )
            ->addTask(
                $this->getTaskGitStackInitWorkingCopy()
                    ->add($readMeFileName)
                    ->commit('Initial commit')
                    ->tag('1.0.0')
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->append(true)
                    ->text("\nLine 1\n")
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->printOutput(false)
                    ->add($readMeFileName)
                    ->commit('Add line 1')
                    ->tag('1.0.1')
            )
            ->addTask(
                $this
                    ->taskWriteToFile($readMeFileName)
                    ->append(true)
                    ->text("\nLine 2\n")
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->printOutput(false)
                    ->add($readMeFileName)
                    ->commit('Add line 2')
                    ->tag('1.0.2')
            );
    }
    // endregion

    // region Task - GitConfigGet
    /**
     * @command config-get:basic
     */
    public function configGetBasic()
    {
        return $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.config-get.basic.', $this->tmpDirBase)
                    ->cwd(true)
            )
            ->addTask($this->getTaskGitStackInitWorkingCopy())
            ->addTask(
                $this
                    ->taskGitConfigGet()
                    ->setSource('local')
                    ->setName('user.email')
            )
            ->addCode(function (RoboStateData $data): int {
                $result = [
                    'user' => [
                        'email' => $data['git.config.user.email'],
                    ],
                ];
                $this->output()->write(Yaml::dump($result, 50));

                /** @var \Symfony\Component\Console\Output\OutputInterface $output */
                //$output = $this->getContainer()->get('output');
                //$output->write(Yaml::dump($result, 50));

                return 0;
            });
    }

    /**
     * @command config-get:copy
     */
    public function configGetCopy()
    {
        $cb = $this->collectionBuilder();
        $cb
            ->addTask($this->taskTmpDir('robo-git.config-get.copy.', $this->tmpDirBase))
            ->addCode(function (RoboStateData $data): int {
                $data['gitConfigNamesToCopy'] = [
                    'user.name',
                    'user.email',
                ];

                $data['srcDir'] = Path::join($data['path'], 'src');
                $data['dstDir'] = Path::join($data['path'], 'dst');

                return 0;
            })
            ->addTask(
                $this
                    ->taskFilesystemStack()
                    ->deferTaskConfiguration('mkdir', 'srcDir')
                    ->deferTaskConfiguration('mkdir', 'dstDir')
            )
            ->addTask($this->getTaskGitStackInitWorkingCopy(
                'srcDir',
                [
                    'user.name' => 'Abc Def',
                    'user.email' => 'abc.def@example.com',
                ]
            ))
            ->addTask($this->getTaskGitStackInitWorkingCopy('dstDir'))
            ->addTask(
                $this
                    ->taskForEach()
                    ->deferTaskConfiguration('setIterable', 'gitConfigNamesToCopy')
                    ->withBuilder(function (CollectionBuilder $builder, int $key, string $name) use ($cb) {
                        $state = $cb->getState();

                        $builder
                            ->addTask(
                                $this
                                    ->taskGitConfigGet()
                                    ->setWorkingDirectory($state['srcDir'])
                                    ->setSource('local')
                                    ->setName($name)
                            )
                            ->addCode(function (RoboStateData $data) use ($name): int {
                                $value = $data["git.config.$name"] ?? null;

                                if ($value === null) {
                                    $data['gitConfigSetCommand'] = sprintf(
                                        'config --unset %s',
                                        escapeshellarg($name)
                                    );

                                    return 0;
                                }

                                $data['gitConfigSetCommand'] = sprintf(
                                    'config %s %s',
                                    escapeshellarg($name),
                                    escapeshellarg($value)
                                );

                                return 0;
                            })
                            ->addTask(
                                $this
                                    ->taskGitStack()
                                    ->dir($state['dstDir'])
                                    ->deferTaskConfiguration('exec', 'gitConfigSetCommand')
                            );
                    })
            )
            ->addTask(
                $this
                    ->taskForEach()
                    ->deferTaskConfiguration('setIterable', 'gitConfigNamesToCopy')
                    ->withBuilder(function (CollectionBuilder $builder, int $key, string $name) use ($cb) {
                        $state = $cb->getState();

                        $builder
                            ->addTask(
                                $this
                                    ->taskGitConfigGet()
                                    ->setWorkingDirectory($state['dstDir'])
                                    ->setSource('local')
                                    ->setName($name)
                            )
                            ->addCode(function (RoboStateData $data) use ($name): int {
                                $value = $data["git.config.$name"];
                                $this->output()->writeln("dstDir.git.config.{$name}: {$value}");

                                return 0;
                            });
                    })
            );

        return $cb;
    }
    // endregion

    /**
     * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Vcs\GitStack
     */
    protected function getTaskGitStackInitWorkingCopy(string $dirStateKey = '', array $config = [])
    {
        $config += [
            'user.name' => 'RoboGit TestRunner',
            'user.email' => 'robo-git.test-runner@example.com',
            'push.default' => 'current',
        ];

        $task = $this
            ->taskGitStack()
            ->printOutput(false)
            ->stopOnFail(true)
            ->exec('init --initial-branch=main');

        if ($dirStateKey) {
            $task->deferTaskConfiguration('dir', $dirStateKey);
        }

        foreach ($config as $name => $value) {
            if ($value === false) {
                continue;
            }

            if ($value === null) {
                $task->exec(sprintf(
                    'config --unset %s',
                    escapeshellarg($name)
                ));

                continue;
            }

            $task->exec(sprintf(
                'config %s %s',
                escapeshellarg($name),
                escapeshellarg($value)
            ));
        }

        return $task;
    }
}
