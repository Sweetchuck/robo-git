<?php

namespace Sweetchuck\Robo\Git\Task;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\GitTaskLoader;

class GitReadStagedFilesTask extends BaseTask implements BuilderAwareInterface, LoggerAwareInterface
{
    use ArgumentPathsTrait;
    use GitTaskLoader;
    use LoggerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git - Read staged files';

    /**
     * {@inheritdoc}
     */
    protected $assets = [
        'workingDirectory' => '',
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
    protected function runHeader()
    {
        $paths = array_keys($this->getPaths(), true);
        $this->printTaskDebug(
            'Read content from <info>{count}</info> staged files from the <info>{workingDirectory}</info> directory',
            [
                'count' => count($paths),
                'workingDirectory' => $this->getWorkingDirectory() ?: '.',
            ]
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runAction()
    {
        $this->actionExitCode = 0;
        $this->actionStdError = '';

        $cmdPattern = '%s --no-pager show :%s';
        $cmdArgs = [
            'git' => escapeshellcmd($this->getGitExecutable()),
            'fileName' => null,
        ];

        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        $this->assets['files'] = [];

        $workingDirectory = $this->assets['workingDirectory'] ?: '.';
        $commandPrefix = ($workingDirectory && $workingDirectory !== '.') ?
            sprintf('cd %s && ', escapeshellarg($workingDirectory))
            : '';

        $fileNames = array_keys($this->getPaths(), true);
        foreach ($fileNames as $fileName) {
            $cmdArgs['fileName'] = escapeshellarg($fileName);

            $this->assets['files'][$fileName] = [
                'fileName' => $fileName,
                'content' => null,
                'command' => vsprintf($cmdPattern, $cmdArgs),
            ];

            if ($this->getCommandOnly()) {
                continue;
            }

            $this->runActionSetContent($commandPrefix, $this->assets['files'][$fileName]);
        }

        return $this;
    }

    protected function runActionSetContent(string $commandPrefix, array &$item)
    {
        $process = $this
            ->getProcessHelper()
            ->run(
                $this->output(),
                "{$commandPrefix}{$item['command']}",
                null,
                $this->processRunCallbackWrapper
            );

        $exitCode = $process->getExitCode();
        if ($exitCode !== 0) {
            $this->printTaskDebug($process->getErrorOutput());

            return $this;
        }

        $item['content'] = $process->getOutput();

        return $this;
    }
}
