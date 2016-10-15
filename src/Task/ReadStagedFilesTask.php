<?php

namespace Cheppers\Robo\Git\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Cheppers\Robo\Git\Utils;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskAccessor;
use Symfony\Component\Process\Process;

/**
 * Class ReadFilesTask.
 *
 * @package Cheppers\Robo\Git\Task
 */
class ReadStagedFilesTask extends BaseTask implements
    AssetJarAwareInterface,
    ContainerAwareInterface,
    OutputAwareInterface
{
    use AssetJarAware;
    use ContainerAwareTrait;
    use IO;
    use TaskAccessor;

    /**
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var array
     */
    protected $assets = [
        'workingDirectory' => '',
        'files' => [],
    ];

    //region Option - workingDirectory
    /**
     * @var string|null
     */
    protected $workingDirectory = null;
    /**
     * @return string|null
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }
    //endregion

    //region Option - gitExecutable
    /**
     * @var string
     */
    protected $gitExecutable = 'git';

    /**
     * @return string
     */
    public function getGitExecutable()
    {
        return $this->gitExecutable;
    }

    /**
     * @param string $gitExecutable
     *
     * @return $this
     */
    public function setGitExecutable($gitExecutable)
    {
        $this->gitExecutable = $gitExecutable;

        return $this;
    }
    //endregion

    //region Option - commandOnly
    /**
     * @var bool
     */
    protected $commandOnly = false;

    /**
     * @return bool
     */
    public function getCommandOnly()
    {
        return $this->commandOnly;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setCommandOnly($value)
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

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     *
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }
    //endregion

    public function __construct(array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'assetJar':
                    $this->setAssetJar($value);
                    break;

                case 'assetJarMapping':
                    $this->setAssetJarMapping($value);
                    break;

                case 'workingDirectory':
                    $this->setWorkingDirectory($value);
                    break;

                case 'gitExecutable':
                    $this->setGitExecutable($value);
                    break;

                case 'commandOnly':
                    $this->setCommandOnly($value);
                    break;

                case 'paths':
                    $this->setPaths($value);
                    break;
            }
        }
    }

    /**
     * @return Result
     */
    public function run()
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
            if (!file_exists("$baseDir/$fileName")) {
                continue;
            }

            $cmdArgs['fileName'] = $fileName;

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
                    $this->setAssetJarValue($key, $this->assets[$key]);
                }
            }
        }

        return Result::success($this, '@todo', $this->assets);
    }

    /**
     * @todo Move the "git diff" to a separated Robo task.
     *
     * @return \string[]
     *
     * @throws \Exception
     */
    protected function getStagedFileNames()
    {
        $cmdPattern = '%s diff --name-only --cached';
        $cmdArgs = [escapeshellcmd($this->getGitExecutable())];


        $paths = $this->getPaths();
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
}
