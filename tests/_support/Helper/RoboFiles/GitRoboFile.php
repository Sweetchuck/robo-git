<?php

namespace Sweetchuck\Robo\Git\Test\Helper\RoboFiles;

use Robo\State\Data as RoboStateData;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Sweetchuck\Robo\Git\Utils;
use Robo\Collection\CollectionBuilder;
use Robo\Tasks as BaseRoboFile;
use Symfony\Component\Yaml\Yaml;

class GitRoboFile extends BaseRoboFile
{
    use GitTaskLoader;

    /**
     * @var string
     */
    protected $tmpDirBase = 'tests/_data/tmp';

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
                $this->output()->write(Yaml::dump($data['gitBranches']), 50);
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
                    ->taskTmpDir('robo-git.', 'tests/_data/tmp')
                    ->cwd(true)
            )
            ->addTask(
                $this
                    ->taskFilesystemStack()
                    ->mkdir('local')
                    ->mkdir('remote')
            )
            ->addTask(
                $this
                    ->taskGitStack()
                    ->stopOnFail(true)
                    ->printOutput(false)
                    ->dir('remote')
                    ->exec('init --bare')
            )
            ->addTask(
                $this
                    ->taskWriteToFile('local/README.md')
                    ->text("# Foo\n")
            )
            ->addTask(
                $this
                    ->getTaskGitStackInitWorkingCopy()
                    ->dir('local')
                    ->exec("remote add 'origin' '../remote'")
                    ->add('README.md')
                    ->commit('Initial commit')
                    ->tag('1.0.0')
                    ->exec('branch "8.x-1.x"')
                    ->exec('push origin 8.x-1.x:8.x-1.x --set-upstream')
            );
    }
    // endregion

    // region Task - GitCurrentBranchTask
    /**
     * @command current-branch:success
     */
    public function currentBranchSuccess(string $branchName): CollectionBuilder
    {
        return $this
            ->currentBranchPrepareTheGitRepo()
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
    protected function currentBranchPrepareTheGitRepo(): CollectionBuilder
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
            ->listFilesPrepareTheGitRepo()
            ->addTask(
                $this
                    ->taskGitListFiles()
                    ->setOutput($this->output())
                    ->setVisibleStdOutput(true)
                    ->setShowStaged(true)
                    ->setFileStatusWithTags(true)
            );
    }

    /**
     * @param string $tmpDir
     */
    protected function listFilesPrepareTheGitRepo(): CollectionBuilder
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
                    ->setOutput($this->output())
                    ->setVisibleStdOutput(false)
                    ->setFromRevName($fromRevName)
                    ->setToRevName($toRevName)
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
            ->readStagedFilesPrepareTheGitRepo()
            ->addTask($this->taskGitReadStagedFiles())
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
            ->readStagedFilesPrepareTheGitRepo()
            ->addTask(
                $this
                    ->taskGitReadStagedFiles()
                    ->setCommandOnly(true)
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
    protected function readStagedFilesPrepareTheGitRepo(): CollectionBuilder
    {
        $cb = $this
            ->collectionBuilder()
            ->addTask(
                $this
                    ->taskTmpDir('robo-git.read-staged-files.', $this->tmpDirBase)
                    ->cwd(true)
            );

        // Created 3 files with the same content.
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
            ->addTask(
                $this
                    ->taskGitTagList()
                    ->setVisibleStdOutput(false)
                    ->setFormat(Utils::$predefinedRefFormats['tag-list.default'])
            )
            ->addCode(function (RoboStateData $data) {
                $tags = $data['gitTags'];
                $shaCounter = 1;
                foreach (array_keys($tags) as $tag) {
                    $tags[$tag]['objectName'] = 'SHA-' . $shaCounter++;
                }
                $this->output()->write(Yaml::dump($tags));

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

    /**
     * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Vcs\GitStack
     */
    protected function getTaskGitStackInitWorkingCopy()
    {
        return $this
            ->taskGitStack()
            ->printOutput(false)
            ->stopOnFail(true)
            ->exec('init')
            ->exec("config user.name 'RoboGit TestRunner'")
            ->exec("config user.email 'robo-git.test-runner@example.com'")
            ->exec("config push.default 'current'");
    }
}
