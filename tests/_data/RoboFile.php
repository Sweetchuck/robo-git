<?php

// @codingStandardsIgnoreStart

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Git\Task\LoadTasks;

    public function withContent()
    {
        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        return $cb->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->prepareTheGitRepo($tmpDir);

            $result = $this
                ->taskGitReadStagedFiles()
                ->setWorkingDirectory($tmpDir)
                ->run();

            foreach ($result['files'] as $file) {
                $this->output()->writeln("--- {$file['fileName']} ---");
                $this->output()->write($file['content']);
            }
        });
    }

    public function withoutContent()
    {
        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        return $cb->addCode(function () {
            $tmpDir = $this->_tmpDir();
            $this->prepareTheGitRepo($tmpDir);

            $result = $this
                ->taskGitReadStagedFiles()
                ->setWorkingDirectory($tmpDir)
                ->setCommandOnly(true)
                ->run();

            /** @var \Robo\Task\Base\ExecStack $execStack */
            $execStack = $this->taskExecStack();
            foreach ($result['files'] as $file) {
                $cmd = sprintf('echo "--- %s ---"', $file['fileName']);
                $cmd .= ' ; ' . sprintf('cd %s', $result['workingDirectory']);
                $cmd .= ' ; ' . $file['command'];
                $execStack->exec($cmd);
            }

            return $execStack->run();
        });
    }

    /**
     * @param string $tmpDir
     */
    protected function prepareTheGitRepo($tmpDir)
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
}
