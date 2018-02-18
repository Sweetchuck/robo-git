<?php

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\BuilderAwareInterface;
use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\GitTaskLoader;

class GitReadStagedFilesTask extends BaseTask implements BuilderAwareInterface
{
    use ArgumentPathsTrait;
    use GitTaskLoader;

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git - Read staged files';

    /**
     * {@inheritdoc}
     */
    protected $assets = [
        'workingDirectory' => '',
        'files' => [],
    ];

    /**
     * @var string[]
     */
    protected $stagedFileNames = [];

    //region Options.
    //region Option - commandOnly
    /**
     * @var bool
     */
    protected $commandOnly = false;

    public function getCommandOnly(): bool
    {
        return $this->commandOnly;
    }

    /**
     * @return $this
     */
    public function setCommandOnly(bool $value)
    {
        $this->commandOnly = $value;

        return $this;
    }
    //endregion
    //endregion

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (isset($options['commandOnly'])) {
            $this->setCommandOnly($options['commandOnly']);
        }

        if (isset($options['paths'])) {
            $this->setPaths($options['paths']);
        }

        return $this;
    }

    protected function runPrepare()
    {
        $this->runPrepareStagedFileNames();

        return $this;
    }

    protected function runHeader()
    {
        $this->printTaskDebug(
            'Read staged file contents from <info>{count}</info> files in directory <info>{workingDirectory}</info>',
            [
                'count' => count($this->stagedFileNames),
                'workingDirectory' => $this->getWorkingDirectory() ?: '.',
            ]
        );

        return $this;
    }

    protected function runAction()
    {
        $this->actionExitCode = 0;
        $this->actionStdError = '';

        $baseDir = $this->getWorkingDirectory() ?: '.';

        $cmdPattern = '%s show :%s';
        $cmdArgs = [
            'git' => escapeshellcmd($this->getGitExecutable()),
            'fileName' => null,
        ];

        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        foreach ($this->stagedFileNames as $fileName) {
            if (!$this->fileExists("$baseDir/$fileName")) {
                continue;
            }

            $cmdArgs['fileName'] = escapeshellarg($fileName);

            $this->assets['files'][$fileName] = [
                'fileName' => $fileName,
                'content' => null,
                'command' => vsprintf($cmdPattern, $cmdArgs),
            ];

            if (!$this->getCommandOnly()) {
                /** @var \Symfony\Component\Process\Process $process */
                $process = new $this->processClass(
                    $this->assets['files'][$fileName]['command'],
                    $this->getWorkingDirectory()
                );

                $exitCode = $process->run();
                // @todo Error handler.
                if ($exitCode === 0) {
                    $this->assets['files'][$fileName]['content'] = $process->getOutput();
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runPrepareStagedFileNames()
    {
        $result = $this
            ->taskGitListStagedFiles()
            ->setWorkingDirectory($this->getWorkingDirectory())
            ->setPaths($this->getPaths())
            ->setFilePathStyle('relativeToWorkingDirectory')
            ->run()
            ->stopOnFail();

        $this->stagedFileNames = $result['files'];

        return $this;
    }

    protected function fileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }
}
