<?php

// @codingStandardsIgnoreStart
use Robo\Collection\CollectionBuilder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class RoboFile extends \Robo\Tasks
    // @codingStandardsIgnoreEnd
{
    use \Cheppers\Robo\Git\GitTaskLoader;

    /**
     * @var array
     */
    protected $composerInfo = [];

    /**
     * @var array
     */
    protected $codeceptionInfo = [];

    /**
     * @var string
     */
    protected $packageVendor = '';

    /**
     * @var string
     */
    protected $packageName = '';

    /**
     * @var string
     */
    protected $binDir = 'vendor/bin';

    /**
     * @var string
     */
    protected $envNamePrefix = '';

    /**
     * @var string
     */
    protected $environment = '';

    protected function getEnvName(string $name): string
    {
        return "{$this->envNamePrefix}_" . strtoupper($name);
    }

    protected function getEnvironment(): string
    {
        if ($this->environment) {
            return $this->environment;
        }

        return getenv($this->getEnvName('environment')) ?: 'dev';
    }

    protected function getPhpExecutable(): string
    {
        return getenv($this->getEnvName('php_executable')) ?: PHP_BINARY;
    }

    protected function getPhpdbgExecutable(): string
    {
        return getenv($this->getEnvName('phpdbg_executable')) ?: PHP_BINDIR . '/phpdbg';
    }

    public function __construct()
    {
        $this
            ->initComposerInfo()
            ->initEnvNamePrefix();
    }

    /**
     * Git "pre-commit" hook callback.
     */
    public function githookPreCommit(): CollectionBuilder
    {
        $this->environment = 'git-hook';

        return $this
            ->collectionBuilder()
            ->addTaskList([
                'lint.composer.lock' => $this->taskComposerValidate(),
                'lint.phpcs.psr2' => $this->getTaskPhpcsLint(),
                'codecept' => $this->getTaskCodecept(),
            ]);
    }

    /**
     * Run the Robo unit tests.
     */
    public function test(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTaskList([
                'codecept' => $this->getTaskCodecept(),
            ]);
    }

    /**
     * Run code style checkers.
     */
    public function lint(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTaskList([
                'lint.composer.lock' => $this->taskComposerValidate(),
                'lint.phpcs.psr2' => $this->getTaskPhpcsLint(),
            ]);
    }

    /**
     * @return $this
     */
    protected function initComposerInfo()
    {
        if ($this->composerInfo || !is_readable('composer.json')) {
            return $this;
        }

        $this->composerInfo = json_decode(file_get_contents('composer.json'), true);
        list($this->packageVendor, $this->packageName) = explode('/', $this->composerInfo['name']);

        if (!empty($this->composerInfo['config']['bin-dir'])) {
            $this->binDir = $this->composerInfo['config']['bin-dir'];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function initCodeceptionInfo()
    {
        if ($this->codeceptionInfo) {
            return $this;
        }

        if (is_readable('codeception.yml')) {
            $this->codeceptionInfo = Yaml::parse(file_get_contents('codeception.yml'));
        } else {
            $this->codeceptionInfo = [
                'paths' => [
                    'log' => 'tests/_output',
                ],
            ];
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function initEnvNamePrefix()
    {
        $this->envNamePrefix = strtoupper(str_replace('-', '_', $this->packageName));

        return $this;
    }

    protected function getTaskPhpcsLint(): CollectionBuilder
    {
        $env = $this->getEnvironment();

        return $this->collectionBuilder()->addCode(function () use ($env) {
            $files = [
                'src/',
                'tests/_data/RoboFile.php',
                'tests/_support/Helper/',
                'tests/acceptance/',
                'tests/unit/',
                'RoboFile.php',
            ];

            /** @var \Robo\Task\Base\ExecStack $execStack */
            $execStack = $this->taskExecStack();
            $numOfCommands = 0;

            $cmdPattern = '%s';
            $cmdArgs = [escapeshellcmd('bin/phpcs')];

            $cmdPattern .= ' --colors';

            $cmdPattern .= ' --standard=%s';
            $cmdArgs[] = 'PSR2';

            $cmdPattern .= ' --report=%s';
            $cmdArgs[] = 'full';

            if ($env === 'git-hook') {
                $gitReadStagedFiles = $this->taskGitReadStagedFiles();
                $gitReadStagedFiles->setPaths($files);
                $result = $gitReadStagedFiles->run();
                if (!empty($result['files'])) {
                    $cmdPattern = 'echo -n %s | ' . $cmdPattern . ' --stdin-path=%s';
                    $cmdArgs = ['fileContent' => ''] + $cmdArgs + ['fileName' => ''];

                    foreach ($result['files'] as $file) {
                        $cmdArgs['fileContent'] = escapeshellarg($file['content']);
                        $cmdArgs['fileName'] = escapeshellarg($file['fileName']);

                        $numOfCommands++;
                        $execStack->exec(vsprintf($cmdPattern, $cmdArgs));
                    }
                }
            } else {
                $cmdPattern .= str_repeat(' %s', count($files));
                foreach ($files as $file) {
                    $cmdArgs[] = escapeshellarg($file);
                }

                $numOfCommands++;
                $execStack->exec(vsprintf($cmdPattern, $cmdArgs));
            }

            return $numOfCommands ? $execStack->run() : 0;
        });
    }

    protected function getTaskCodecept(): CollectionBuilder
    {
        $environment = $this->getEnvironment();
        $withCoverage = $environment !== 'git-hook';
        $withUnitReport = $environment !== 'git-hook';
        $logDir = $this->getLogDir();

        $cmdArgs = [];
        if ($this->isPhpDbgAvailable()) {
            $cmdPattern = '%s -qrr %s';
            $cmdArgs[] = escapeshellcmd($this->getPhpdbgExecutable());
            $cmdArgs[] = escapeshellarg("{$this->binDir}/codecept");
        } else {
            $cmdPattern = '%s';
            $cmdArgs[] = escapeshellcmd("{$this->binDir}/codecept");
        }

        $cmdPattern .= ' --ansi';
        $cmdPattern .= ' --verbose';

        $tasks = [];
        if ($withCoverage) {
            $cmdPattern .= ' --coverage=%s';
            $cmdArgs[] = escapeshellarg('coverage/coverage.serialized');

            $cmdPattern .= ' --coverage-xml=%s';
            $cmdArgs[] = escapeshellarg('coverage/coverage.xml');

            $cmdPattern .= ' --coverage-html=%s';
            $cmdArgs[] = escapeshellarg('coverage/html');

            $tasks['prepareCoverageDir'] = $this
                ->taskFilesystemStack()
                ->mkdir("$logDir/coverage");
        }

        if ($withUnitReport) {
            $cmdPattern .= ' --xml=%s';
            $cmdArgs[] = escapeshellarg('junit/junit.xml');

            $cmdPattern .= ' --html=%s';
            $cmdArgs[] = escapeshellarg('junit/junit.html');

            $tasks['prepareJUnitDir'] = $this
                ->taskFilesystemStack()
                ->mkdir("$logDir/junit");
        }

        $cmdPattern .= ' run';

        if ($environment === 'jenkins') {
            // Jenkins has to use a post-build action to mark the build "unstable".
            $cmdPattern .= ' || [[ "${?}" == "1" ]]';
        }

        $tasks['runCodeception'] = $this->taskExec(vsprintf($cmdPattern, $cmdArgs));

        return $this
            ->collectionBuilder()
            ->addTaskList($tasks);
    }

    protected function isPhpExtensionAvailable(string $extension): bool
    {
        $command = sprintf('%s -m', escapeshellcmd($this->getPhpExecutable()));

        $process = new Process($command);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \RuntimeException('@todo');
        }

        return in_array($extension, explode("\n", $process->getOutput()));
    }

    protected function isPhpDbgAvailable(): bool
    {
        $command = sprintf(
            "%s -qrr ''",
            escapeshellcmd($this->getPhpdbgExecutable())
        );

        return (new Process($command))->run() === 0;
    }

    protected function getLogDir(): string
    {
        $this->initCodeceptionInfo();

        return !empty($this->codeceptionInfo['paths']['log']) ?
            $this->codeceptionInfo['paths']['log']
            : 'tests/_output';
    }
}
