<?php

namespace Cheppers\Robo\Git\Task;

use Cheppers\Robo\Git\Utils;
use Robo\Result;
use Symfony\Component\Process\Process;

class GitReadStagedFilesTask extends BaseTask
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git read staged files';

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

    //region Option - paths
    /**
     * @var string[]
     */
    protected $paths = [];

    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

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
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'commandOnly':
                    $this->setCommandOnly($value);
                    break;

                case 'paths':
                    $this->setPaths($value);
                    break;
            }
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

        if ($this->hasAssetJar()) {
            foreach ($this->assets as $key => $value) {
                if ($this->getAssetJarMap($key)) {
                    $this->setAssetJarValue($key, $value);
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
