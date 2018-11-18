<?php

namespace Sweetchuck\Robo\Git\Task;

use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Robo\Contract\InflectionInterface;
use Robo\TaskAccessor;
use Sweetchuck\Robo\Git\Utils;
use League\Container\ContainerAwareInterface;
use Robo\Common\IO;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Process\Process;

abstract class BaseTask extends RoboBaseTask implements
    ContainerAwareInterface,
    OutputAwareInterface,
    InflectionInterface
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
    protected $command = '';

    /**
     * @var null|\Closure
     */
    protected $processRunCallbackWrapper = null;

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
        if (isset($options['assetNamePrefix'])) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        if (isset($options['workingDirectory'])) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (isset($options['gitExecutable'])) {
            $this->setGitExecutable($options['gitExecutable']);
        }

        if (isset($options['stdOutputVisible'])) {
            $this->setVisibleStdOutput($options['stdOutputVisible']);
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

        $cmdArgsNormalPattern = [];
        $cmdArgsNormalArgs = [];

        $cmdArgsExtraPattern = [];
        $cmdArgsExtraArgs = [];

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

                case 'flag:true-value':
                    if ($option['value'] !== null) {
                        $pattern = '--' . ($option['value'] === false ? 'no-' : '') . $optionName;
                        if ($option['value'] && $option['value'] !== true) {
                            $pattern .= ' %s';
                            $cmdOptionsArgs[] = escapeshellarg($option['value']);
                        }
                        $cmdOptionsPattern[] = $pattern;
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

                case 'state:value-required':
                    if ($option['value']) {
                        $cmdOptionsPattern[] = '--' . ($option['state'] ? '' : 'no-') . "$optionName %s";
                        $cmdOptionsArgs[] = escapeshellarg($option['value']);
                    }
                    break;

                case 'state:value-optional':
                    if ($option['state'] !== null) {
                        $pattern = '--' . ($option['state'] ? '' : 'no-') . $optionName;
                        if ($option['value']) {
                            $pattern .= ' %s';
                            $cmdOptionsArgs[] = escapeshellarg($option['value']);
                        }
                        $cmdOptionsPattern[] = $pattern;
                    }
                    break;

                case 'arg-extra:list':
                    $args = Utils::filterEnabled($option['value']);
                    if ($args) {
                        $cmdArgsExtraPattern[] = trim(str_repeat(' %s', count($args)));
                        foreach ($args as $arg) {
                            $cmdArgsExtraArgs[] = escapeshellarg($arg);
                        }
                    }
                    break;

                case 'arg-normal':
                    if ($option['value'] !== null) {
                        $cmdArgsNormalPattern[] = '%s';
                        $cmdArgsNormalArgs[] = escapeshellarg($option['value']);
                    }
                    break;
            }
        }

        $cmdFragments[] = vsprintf(implode(' ', $cmdOptionsPattern), $cmdOptionsArgs);

        if ($cmdArgsNormalPattern) {
            $cmdFragments[] = vsprintf(implode(' ', $cmdArgsNormalPattern), $cmdArgsNormalArgs);
        }

        if ($cmdArgsExtraPattern) {
            $cmdFragments[] = '--';
            $cmdFragments[] = vsprintf(implode(' ', $cmdArgsExtraPattern), $cmdArgsExtraArgs);
        }

        return implode(' ', array_filter($cmdFragments));
    }

    /**
     * {@inheritdoc}
     */
    public function run(): Result
    {
        return $this
            ->runValidate()
            ->runPrepare()
            ->runHeader()
            ->runAction()
            ->runProcessOutputs()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runValidate()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function runPrepare()
    {
        $this->runPrepareProcessRunCallbackWrapper();
        $this->command = $this->getCommand();

        return $this;
    }

    protected function runPrepareProcessRunCallbackWrapper()
    {
        $this->processRunCallbackWrapper = function (string $type, string $data): void {
            $this->processRunCallback($type, $data);
        };

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
        $process = $this
            ->getProcessHelper()
            ->run($this->output(), $this->command, null, $this->processRunCallbackWrapper);

        $this->actionExitCode = $process->getExitCode();
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

    protected function processRunCallback(string $type, string $data): void
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

    protected function getProcessHelper(): ProcessHelper
    {
        return $this
            ->getContainer()
            ->get('application')
            ->getHelperSet()
            ->get('process');
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
