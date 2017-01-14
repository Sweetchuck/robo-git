<?php

namespace Helper\Module;

use Codeception\Module as CodeceptionModule;
use Symfony\Component\Process\Process;

/**
 * Wrapper for basic shell commands and shell output.
 */
class Cli extends CodeceptionModule
{
    /**
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * @var null|int
     */
    protected $exitCode = null;

    /**
     * @var null|string
     */
    protected $stdOutput = null;

    /**
     * @var null|string
     */
    protected $stdError = null;

    // @codingStandardsIgnoreStart
    public function _cleanup()
    {
        // @codingStandardsIgnoreEnd
        $this->process = null;
    }

    /**
     * Executes a shell command.
     *
     * @return $this
     */
    public function runShellCommand(string $command)
    {
        $this->process = new Process($command);
        $this->exitCode = $this->process->run();
        $this->stdOutput = $this->process->getOutput();
        $this->stdError = $this->process->getErrorOutput();

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function getStdOutput(): ?string
    {
        return $this->stdOutput;
    }

    public function getStdError(): ?string
    {
        return $this->stdError;
    }
}
