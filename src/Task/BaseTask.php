<?php

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\AssetJar\AssetJarAware;
use Sweetchuck\AssetJar\AssetJarAwareInterface;
use Sweetchuck\Robo\Git\Utils;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskAccessor;
use Robo\TaskInfo;
use Symfony\Component\Process\Process;

abstract class BaseTask extends RoboBaseTask implements
    ContainerAwareInterface,
    OutputAwareInterface
{
    use ContainerAwareTrait;
    use IO;
    use TaskAccessor;

    /**
     * @var string
     */
    protected $taskName = '';

    /**
     * @var array
     */
    protected $assets = [];

    //region Options.

    // region Option - assetNamePrefix.
    /**
     * @var string
     */
    protected $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    /**
     * @return $this
     */
    public function setAssetNamePrefix(string $value)
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    // endregion

    //region Option - workingDirectory
    /**
     * @var string
     */
    protected $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @return $this
     */
    public function setWorkingDirectory(string $workingDirectory)
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

    public function getGitExecutable(): string
    {
        return $this->gitExecutable;
    }

    /**
     * @return $this
     */
    public function setGitExecutable(string $gitExecutable)
    {
        $this->gitExecutable = $gitExecutable;

        return $this;
    }
    //endregion

    //region Option - visibleStdOutput
    /**
     * @var bool
     */
    protected $visibleStdOutput = false;

    public function isStdOutputVisible(): bool
    {
        return $this->visibleStdOutput;
    }

    /**
     * @return $this
     */
    public function setVisibleStdOutput(bool $visible)
    {
        $this->visibleStdOutput = $visible;

        return $this;
    }
    //endregion

    //endregion

    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var int
     */
    protected $actionExitCode = 0;

    /**
     * @var string
     */
    protected $actionStdOutput = '';

    /**
     * @var string
     */
    protected $actionStdError = '';

    /**
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var string
     */
    protected $command = '';

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    protected function getOptions(): array
    {
        return [];
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            switch ($name) {
                case 'assetNamePrefix':
                    $this->setAssetNamePrefix($value);
                    break;

                case 'workingDirectory':
                    $this->setWorkingDirectory($value);
                    break;

                case 'gitExecutable':
                    $this->setGitExecutable($value);
                    break;

                case 'stdOutputVisible':
                    $this->setVisibleStdOutput($value);
                    break;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        $options = $this->getOptions();

        $cmdFragments = [];

        $cmdOptionsPattern = [];
        $cmdOptionsArgs = [];

        $cmdArgsPattern = [];
        $cmdArgsArgs = [];

        $workingDir = $this->getWorkingDirectory();
        if ($workingDir) {
            $cmdFragments[] = 'cd ' . escapeshellarg($workingDir);
            $cmdFragments[] = '&&';
        }

        $cmdFragments[] = escapeshellcmd($this->getGitExecutable());
        if ($this->action) {
            $cmdFragments[] = $this->action;
        }

        foreach ($options as $optionName => $option) {
            switch ($option['type']) {
                case 'flag':
                    if ($option['value']) {
                        $cmdOptionsPattern[] = $optionName;
                    }
                    break;

                case 'value:optional':
                    if ($option['value'] !== null) {
                        if ($option['value'] === '') {
                            $cmdOptionsPattern[] = $optionName;
                        } else {
                            $cmdOptionsPattern[] = "$optionName %s";
                            $cmdOptionsArgs[] = escapeshellarg($option['value']);
                        }
                    }
                    break;

                case 'value:required':
                    if ($option['value']) {
                        $cmdOptionsPattern[] = "$optionName %s";
                        $cmdOptionsArgs[] = escapeshellarg($option['value']);
                    }
                    break;

                case 'value:multi':
                    $items = Utils::filterEnabled($option['value']);
                    if ($items) {
                        $cmdOptionsPattern[] = $optionName . str_repeat(' %s', count($items));
                        foreach ($items as $item) {
                            $cmdOptionsArgs[] = escapeshellarg($item);
                        }
                    }
                    break;

                case 'value:state':
                    if ($option['state'] !== null) {
                        $pattern = '--' . ($option['state'] ? '' : 'no-') . $optionName;
                        if ($option['value']) {
                            $pattern .= ' %s';
                            $cmdOptionsArgs[] = escapeshellarg($option['value']);
                        }
                        $cmdOptionsPattern[] = $pattern;
                    }
                    break;

                case 'arg:list':
                    $args = Utils::filterEnabled($option['value']);
                    if ($args) {
                        $cmdArgsPattern[] = trim(str_repeat(' %s', count($args)));
                        foreach ($args as $arg) {
                            $cmdArgsArgs[] = escapeshellarg($arg);
                        }
                    }
                    break;
            }
        }

        $cmdFragments[] = vsprintf(implode(' ', $cmdOptionsPattern), $cmdOptionsArgs);

        if ($cmdArgsPattern) {
            $cmdFragments[] = '--';
            $cmdFragments[] = vsprintf(implode(' ', $cmdArgsPattern), $cmdArgsArgs);
        }

        return implode(' ', array_filter($cmdFragments));
    }

    /**
     * {@inheritdoc}
     */
    public function run(): Result
    {
        return $this
            ->runPrepare()
            ->runHeader()
            ->runAction()
            ->runProcessOutputs()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runPrepare()
    {
        $this->command = $this->getCommand();

        return $this;
    }

    /**
     * @return $this
     */
    protected function runHeader()
    {
        $this->printTaskDebug($this->command);

        return $this;
    }

    /**
     * @return $this
     */
    protected function runAction()
    {
        /** @var \Symfony\Component\Process\Process $process */
        $process = new $this->processClass($this->command);

        $this->actionExitCode = $process->run(function ($type, $data) {
            $this->runCallback($type, $data);
        });
        $this->actionStdOutput = $process->getOutput();
        $this->actionStdError = $process->getErrorOutput();

        return $this;
    }

    /**
     * @return $this
     */
    protected function runProcessOutputs()
    {
        return $this;
    }

    protected function runReturn(): Result
    {
        $assetNamePrefix = $this->getAssetNamePrefix();
        if ($assetNamePrefix === '') {
            $data = $this->assets;
        } else {
            $data = [];
            foreach ($this->assets as $key => $value) {
                $data["{$assetNamePrefix}{$key}"] = $value;
            }
        }

        return new Result(
            $this,
            $this->actionExitCode,
            $this->actionStdError,
            $data
        );
    }

    protected function runCallback(string $type, string $data): void
    {
        switch ($type) {
            case Process::OUT:
                if ($this->isStdOutputVisible()) {
                    $this->output()->write($data);
                }
                break;

            case Process::ERR:
                $this->printTaskError($data);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        if (!$context) {
            $context = [];
        }

        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }
}
