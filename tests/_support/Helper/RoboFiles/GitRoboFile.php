<?php

namespace Sweetchuck\Robo\Git\Test\Helper\RoboFiles;

use Sweetchuck\Robo\Git\GitTaskLoader;
use Sweetchuck\Robo\Git\Utils;
use Robo\Collection\CollectionBuilder;
use Robo\Tasks as BaseRoboFile;
use Symfony\Component\Yaml\Yaml;

class GitRoboFile extends BaseRoboFile
{
    use GitTaskLoader;

    public function readStagedFilesWithContent()
    {
        return $this->collectionBuilder()->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->readStagedFilesPrepareTheGitRepo($tmpDir);

            $result = $this
                ->taskGitReadStagedFiles()
                ->setWorkingDirectory($tmpDir)
                ->run();

            $this->output()->writeln('*** BEGIN Output ***');
            foreach ($result['files'] as $file) {
                $this->output()->writeln("--- {$file['fileName']} ---");
                $this->output()->write($file['content']);
            }
            $this->output()->writeln('*** END Output ***');

            return $result->getExitCode();
        });
    }

    public function readStagedFilesWithoutContent()
    {
        return $this->collectionBuilder()->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->readStagedFilesPrepareTheGitRepo($tmpDir);

            $result = $this
                ->taskGitReadStagedFiles()
                ->setWorkingDirectory($tmpDir)
                ->setCommandOnly(true)
                ->run();

            $this->output()->writeln('*** BEGIN Output ***');
            foreach ($result['files'] as $file) {
                $this->output()->writeln("--- {$file['fileName']} ---");
                $this->output()->writeln("{$file['command']}");
            }
            $this->output()->writeln('*** END Output ***');

            return $result->getExitCode();
        });
    }

    public function listFiles()
    {
        return $this->collectionBuilder()->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->listFilesPrepareTheGitRepo($tmpDir);

            return $this
                ->taskGitListFiles()
                ->setOutput($this->output())
                ->setWorkingDirectory($tmpDir)
                ->setVisibleStdOutput(true)
                ->setShowStaged(true)
                ->setFileStatusWithTags(true)
                ->run();
        });
    }

    public function tagListHuman(): CollectionBuilder
    {
        return $this->collectionBuilder()->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->tagListPrepareGitRepo($tmpDir);

            return $this
                ->taskGitTagList()
                ->setWorkingDirectory($tmpDir)
                ->setVisibleStdOutput(true)
                ->run()
                ->stopOnFail();
        });
    }

    public function tagListMachine(): CollectionBuilder
    {
        return $this->collectionBuilder()->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->tagListPrepareGitRepo($tmpDir);

            $result = $this
                ->taskGitTagList()
                ->setWorkingDirectory($tmpDir)
                ->setVisibleStdOutput(false)
                ->setFormat(Utils::$tagListFormats[Utils::$defaultTagListFormat])
                ->run()
                ->stopOnFail();

            $tags = $result['tags'];
            $shaCounter = 1;
            foreach (array_keys($tags) as $tag) {
                $tags[$tag]['objectName'] = 'SHA-' . $shaCounter++;
            }

            $this->output()->write(Yaml::dump($tags));

            return $result;
        });
    }

    /**
     * @command num-of-commits-between:basic
     */
    public function numOfCommitsBetweenBasic(string $fromRevName, string $toRevName): CollectionBuilder
    {
        return $this->collectionBuilder()->addCode(function () use ($fromRevName, $toRevName) {
            $tmpDir = $this->_tmpDir();

            $result = $this
                ->numOfCommitsBetweenPrepareGitRepo($tmpDir)
                ->taskGitNumOfCommitsBetween()
                ->setWorkingDirectory($tmpDir)
                ->setOutput($this->output())
                ->setVisibleStdOutput(false)
                ->setFromRevName($fromRevName)
                ->setToRevName($toRevName)
                ->run()
                ->stopOnFail();

            $this
                ->output()
                ->writeln($result['numOfCommits']);

            return $result;
        });
    }

    protected function numOfCommitsBetweenPrepareGitRepo(string $tmpDir)
    {
        $readMeContent = "# Foo\n";
        $readMeFileName = "$tmpDir/README.md";

        $this
            ->taskWriteToFile($readMeFileName)
            ->text($readMeContent)
            ->run()
            ->stopOnFail();

        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->exec('init')
            ->add($readMeFileName)
            ->commit('Initial commit')
            ->tag('1.0.0')
            ->run()
            ->stopOnFail();

        $this
            ->taskWriteToFile($readMeFileName)
            ->append()
            ->line('New line 1')
            ->run()
            ->stopOnFail();
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->add($readMeFileName)
            ->commit('Add new line 1 to README.md')
            ->tag('1.0.1')
            ->run()
            ->stopOnFail();

        $this
            ->taskWriteToFile($readMeFileName)
            ->append()
            ->line('New line 2')
            ->run()
            ->stopOnFail();
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->add($readMeFileName)
            ->commit('Add new line 2 to README.md')
            ->tag('1.0.3')
            ->run()
            ->stopOnFail();

        return $this;
    }

    /**
     * @param string $tmpDir
     */
    protected function readStagedFilesPrepareTheGitRepo($tmpDir)
    {
        // Created 3 files with the same content.
        foreach (['a', 'b', 'c'] as $fileName) {
            $this
                ->taskWriteToFile("$tmpDir/$fileName.php")
                ->lines([
                    '<?php',
                    '',
                    '$a = "foo";',
                ])
                ->run();
        }

        // Add two of them to the stage.
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->exec('init')
            ->add('a.php')
            ->add('b.php')
            ->run();

        // Change all of them.
        // Now the staged content is different than the written one.
        foreach (['a', 'b', 'c'] as $fileName) {
            $this
                ->taskWriteToFile("$tmpDir/$fileName.php")
                ->append(true)
                ->replace('foo', 'bar')
                ->run();
        }
    }

    /**
     * @param string $tmpDir
     */
    protected function listFilesPrepareTheGitRepo($tmpDir)
    {
        // Created 3 files with the same content.
        foreach (['a', 'b', 'c'] as $fileName) {
            $this
                ->taskWriteToFile("$tmpDir/$fileName.php")
                ->lines([
                    '<?php',
                    '',
                    '$a = "foo";',
                ])
                ->run();
        }

        // Add two of them to the stage.
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->exec('init')
            ->add('a.php')
            ->add('b.php')
            ->run();

        // Change one of them.
        // Now the staged content is different than the written one.
        foreach (['b'] as $fileName) {
            $this
                ->taskWriteToFile("$tmpDir/$fileName.php")
                ->append(true)
                ->replace('foo', 'bar')
                ->run();
        }
    }

    protected function tagListPrepareGitRepo(string $tmpDir): void
    {
        $readMeContent = "# Foo\n";
        $readMeFileName = "$tmpDir/README.md";

        $this
            ->taskWriteToFile($readMeFileName)
            ->text($readMeContent)
            ->run()
            ->stopOnFail();

        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->exec('init')
            ->add($readMeFileName)
            ->commit('Initial commit')
            ->tag('1.0.0')
            ->run()
            ->stopOnFail();

        $readMeContent .= "\nLine 1\n";
        file_put_contents($readMeFileName, $readMeContent);
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->add($readMeFileName)
            ->commit('Add line 1')
            ->tag('1.0.1')
            ->run()
            ->stopOnFail();

        $readMeContent .= "Line 2\n";
        file_put_contents($readMeFileName, $readMeContent);
        $this
            ->taskGitStack()
            ->printOutput(false)
            ->dir($tmpDir)
            ->add($readMeFileName)
            ->commit('Add line 2')
            ->tag('1.0.2')
            ->run()
            ->stopOnFail();
    }
}
