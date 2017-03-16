<?php

namespace Cheppers\Robo\Git\Test\Helper\RoboFiles;

use Cheppers\AssetJar\AssetJar;
use Cheppers\Robo\Git\GitTaskLoader;
use Cheppers\Robo\Git\Utils;
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

            $assetJar = new AssetJar();

            $result = $this
                ->taskGitTagList()
                ->setAssetJar($assetJar)
                ->setAssetJarMapping(['tags' => ['tags']])
                ->setWorkingDirectory($tmpDir)
                ->setVisibleStdOutput(false)
                ->setFormat(Utils::$tagListFormats[Utils::$defaultTagListFormat])
                ->run()
                ->stopOnFail();

            $tags = $assetJar->getValue(['tags']);
            $sha_counter = 1;
            foreach (array_keys($tags) as $tag) {
                $tags[$tag]['objectName'] = 'SHA-' . $sha_counter++;
            }

            $this->output()->write(Yaml::dump($tags));

            return $result;
        });
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
            ->printed(false)
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
            ->printed(false)
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
            ->printed(false)
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
            ->printed(false)
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
            ->printed(false)
            ->dir($tmpDir)
            ->add($readMeFileName)
            ->commit('Add line 2')
            ->tag('1.0.2')
            ->run()
            ->stopOnFail();
    }
}
