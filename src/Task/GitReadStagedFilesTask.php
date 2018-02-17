<?php

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\Utils;
use Robo\Result;
use Symfony\Component\Process\Process;

class GitReadStagedFilesTask extends BaseTask
{
    use ArgumentPathsTrait;

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

    /**
     * {@inheritdoc}
     */
    public function run(): Result
    {
        $fileNames = $this->getStagedFileNames();
        $baseDir = $this->getWorkingDirectory() ?: '.';

        $cmdPattern = '%s show :%s';
        $cmdArgs = [
            'git' => escapeshellcmd($this->getGitExecutable()),
            'fileName' => null,
        ];

        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        foreach ($fileNames as $fileName) {
            if (!$this->fileExists("$baseDir/$fileName")) {
                continue;
            }

            $cmdArgs['fileName'] = escapeshellarg($fileName);

            $file = [
                'fileName' => $fileName,
                'content' => null,
                'command' => vsprintf($cmdPattern, $cmdArgs),
            ];

            if ($this->getCommandOnly()) {
                $this->assets['files'][$fileName] = $file;
            } else {
                /** @var Process $process */
                $process = new $this->processClass($file['command'], $this->getWorkingDirectory());

                $exitCode = $process->run();
                if ($exitCode === 0) {
                    $file['content'] = $process->getOutput();
                    $this->assets['files'][$fileName] = $file;
                } else {
                    // @todo Error handler.
                }
            }
        }

        return Result::success($this, '@todo', $this->assets);
    }

    /**
     * @todo Move the "git diff" to a separated Robo task.
     *
     * @return string[]
     *
     * @throws \Exception
     */
    protected function getStagedFileNames(): array
    {
        $cmdPattern = '%s diff --name-only --cached';
        $cmdArgs = [escapeshellcmd($this->getGitExecutable())];

        $paths = Utils::filterEnabled($this->getPaths());
        if ($paths) {
            $cmdPattern .= ' --' . str_repeat(' %s', count($paths));
            foreach ($paths as $path) {
                $cmdArgs[] = Utils::escapeShellArgWithWildcard($path);
            }
        }
        $command = vsprintf($cmdPattern, $cmdArgs);

        /** @var Process $process */
        $process = new $this->processClass($command, $this->getWorkingDirectory());
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \Exception("Failed to run the following command `{$command}`", 42);
        }

        return explode("\n", trim($process->getOutput(), "\n"));
    }

    protected function fileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }
}
