<?php

// @codingStandardsIgnoreStart

/**
 * Class RoboFile.
 */
class RoboFile extends \Robo\Tasks
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Git\Task\LoadTasks;

    public function dummy()
    {
        /** @var \Robo\Collection\CollectionBuilder $cb */
        $cb = $this->collectionBuilder();

        return $cb->addCode(function () {
            $tmpDir = $this->_tmpDir();

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

            $this
                ->taskGitStack()
                ->dir($tmpDir)
                ->exec('init')
                ->add('a.php')
                ->add('b.php')
                ->run();

            foreach (['a', 'b', 'c'] as $fileName) {
                $this
                    ->taskWriteToFile("$tmpDir/$fileName.php")
                    ->append(true)
                    ->replace('foo', 'bar')
                    ->run();
            }

            $result = $this
                ->taskGitReadStagedFiles()
                ->setWorkingDirectory($tmpDir)
                ->run();

            $this->output()->writeln(print_r((array) $result, true));
        });
    }
}
