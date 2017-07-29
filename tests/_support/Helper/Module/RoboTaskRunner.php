<?php

namespace Sweetchuck\Robo\Git\Test\Helper\Module;

use Codeception\Module as CodeceptionModule;
use Sweetchuck\Robo\Git\Test\Helper\Dummy\Output as DummyOutput;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Output\OutputInterface;

class RoboTaskRunner extends CodeceptionModule
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\Helper\Dummy\Output[]
     */
    protected $roboTaskStdOutput = [];

    /**
     * @var int[]
     */
    protected $roboTaskExitCode = [];

    public function getRoboTaskExitCode(string $id): int
    {
        return $this->roboTaskExitCode[$id];
    }

    public function getRoboTaskStdOutput(string $id): string
    {
        return $this->roboTaskStdOutput[$id]->output;
    }

    public function getRoboTaskStdError(string $id): string
    {
        /** @var \Sweetchuck\Robo\Git\Test\Helper\Dummy\Output $errorOutput */
        $errorOutput = $this->roboTaskStdOutput[$id]->getErrorOutput();

        return $errorOutput->output;
    }

    public function runRoboTask(string $id, string $class, string ...$args): void
    {
        if (isset($this->roboTaskStdOutput[$id])) {
            throw new \InvalidArgumentException();
        }

        $config = [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            'colors' => false,
        ];
        $this->roboTaskStdOutput[$id] = new DummyOutput($config);

        array_unshift($args, 'RoboTaskRunner.php', '--no-ansi');

        $containerBackup = Robo::hasContainer() ? Robo::getContainer() : null;
        $container = Robo::createDefaultContainer(null, $this->roboTaskStdOutput[$id]);
        $container->add('output', $this->roboTaskStdOutput[$id], false);
        Robo::setContainer($container);

        $this->roboTaskExitCode[$id] = (new Runner($class))
            ->setContainer($container)
            ->execute($args);

        if ($containerBackup) {
            Robo::setContainer($containerBackup);
        }
    }
}
