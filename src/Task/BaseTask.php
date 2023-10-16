<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Consolidation\AnnotatedCommand\Output\OutputAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\InflectionInterface;
use Robo\TaskAccessor;
use Sweetchuck\Robo\Git\Utils;
use League\Container\ContainerAwareInterface;
use Robo\Common\IO;
use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;
use Sweetchuck\Utils\Filter\EnabledFilter;
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

    protected string $taskName = '';

    protected array $assets = [];

    //region Options.

    protected string $shell = 'bash';

    // region Option - assetNamePrefix.
    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $value): static
    {
        $this->assetNamePrefix = $value;

        return $this;
    }
    // endregion

    //region Option - workingDirectory
    protected string $workingDirectory = '';

    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function setWorkingDirectory(string $workingDirectory): static
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }
    //endregion

    //region Option - gitExecutable
    protected string $gitExecutable = 'git';

    public function getGitExecutable(): string
    {
        return $this->gitExecutable;
    }

    public function setGitExecutable(string $gitExecutable): static
    {
        $this->gitExecutable = $gitExecutable;

        return $this;
    }
    //endregion

    //region Option - visibleStdOutput
    protected bool $visibleStdOutput = false;

    public function isStdOutputVisible(): bool
    {
        return $this->visibleStdOutput;
    }

    public function setVisibleStdOutput(bool $visible): static
    {
        $this->visibleStdOutput = $visible;

        return $this;
    }
    //endregion

    //endregion

    protected string $action = '';

    protected int $actionExitCode = 0;

    protected string $actionStdOutput = '';

    protected string $actionStdError = '';

    protected string $command = '';

    protected ?\Closure $processRunCallbackWrapper = null;

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }

    protected function getOptions(): array
    {
        return [];
    }

    public function setOptions(array $options): static
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        if (array_key_exists('workingDirectory', $options)) {
            $this->setWorkingDirectory($options['workingDirectory']);
        }

        if (array_key_exists('gitExecutable', $options)) {
            $this->setGitExecutable($options['gitExecutable']);
        }

        if (array_key_exists('stdOutputVisible', $options)) {
            $this->setVisibleStdOutput($options['stdOutputVisible']);
        }

        if (!empty($options['output'])) {
            $this->setOutput($options['output']);
        }

        return $this;
    }

    /**
     * @return string
     *
     * @see \Robo\Contract\CommandInterface
     */
    public function getCommand()
    {
        $options = $this->getOptions();

        $cmdExecutable = [];

        $cmdMainOptionsPattern = [];
        $cmdMainOptionsArgs = [];

        $cmdOptionsPattern = [];
        $cmdOptionsArgs = [];

        $cmdArgsNormalPattern = [];
        $cmdArgsNormalArgs = [];

        $cmdArgsExtraPattern = [];
        $cmdArgsExtraArgs = [];

        $enabledFilter = new EnabledFilter();

        foreach ($options as $optionName => $option) {
            switch ($option['type']) {
                case 'flag:main':
                    if ($option['value']) {
                        $cmdMainOptionsPattern[] = $optionName;
                    }
                    break;

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
                            $cmdOptionsArgs[] = escapeshellarg((string) $option['value']);
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
                    $items = array_filter($option['value'], $enabledFilter);
                    if (gettype(reset($items)) === 'boolean') {
                        $items = array_keys($items);
                    }

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
                    $args = array_filter($option['value'], $enabledFilter);
                    if (gettype(reset($args)) === 'boolean') {
                        $args = array_keys($args);
                    }

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

        $workingDir = $this->getWorkingDirectory();
        if ($workingDir) {
            $cmdExecutable[] = 'cd ' . escapeshellarg($workingDir);
            $cmdExecutable[] = '&&';
        }

        $cmdExecutable[] = escapeshellcmd($this->getGitExecutable());

        $cmdExecutable[] = vsprintf(implode(' ', $cmdMainOptionsPattern), $cmdMainOptionsArgs);

        if ($this->action) {
            $cmdExecutable[] = $this->action;
        }

        $cmdExecutable[] = vsprintf(implode(' ', $cmdOptionsPattern), $cmdOptionsArgs);

        if ($cmdArgsNormalPattern) {
            $cmdExecutable[] = vsprintf(implode(' ', $cmdArgsNormalPattern), $cmdArgsNormalArgs);
        }

        if ($cmdArgsExtraPattern) {
            $cmdExecutable[] = '--';
            $cmdExecutable[] = vsprintf(implode(' ', $cmdArgsExtraPattern), $cmdArgsExtraArgs);
        }

        return implode(' ', array_filter($cmdExecutable, 'strlen'));
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

    protected function runValidate(): static
    {
        return $this;
    }

    protected function runPrepare(): static
    {
        $this->runPrepareProcessRunCallbackWrapper();
        $this->command = $this->getCommand();

        return $this;
    }

    protected function runPrepareProcessRunCallbackWrapper(): static
    {
        $this->processRunCallbackWrapper = function (string $type, string $data): void {
            $this->processRunCallback($type, $data);
        };

        return $this;
    }

    protected function runHeader(): static
    {
        $this->printTaskDebug($this->command);

        return $this;
    }

    protected function runAction(): static
    {
        $processHelper = $this->getProcessHelper();
        $process = $processHelper->run(
            $this->output(),
            [
                $this->shell,
                '-c',
                $this->command
            ],
            null,
            $this->processRunCallbackWrapper
        );

        $this->actionExitCode = $process->getExitCode();
        $this->actionStdOutput = $process->getOutput();
        $this->actionStdError = $process->getErrorOutput();

        return $this;
    }

    protected function runProcessOutputs(): static
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
            $this->getTaskExitCode(),
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

    protected function getTaskExitCode(): int
    {
        return $this->actionExitCode;
    }
}
