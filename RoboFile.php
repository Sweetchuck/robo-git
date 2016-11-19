<?php

// @codingStandardsIgnoreStart
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RoboFile.
 */
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
    protected $phpExecutable = 'php';

    /**
     * @var string
     */
    protected $phpdbgExecutable = 'phpdbg';

    //region Property - environment
    /**
     * @var string
     */
    protected $environment = null;

    /**
     * @return string
     */
    protected function getEnvironment()
    {
        if ($this->environment) {
            return $this->environment;
        }

        $packageNameUpper = strtoupper(str_replace('-', '_', $this->packageName));

        return getenv("{$packageNameUpper}_ENVIRONMENT") ?: 'dev';
    }
    //endregion

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this->initComposerInfo();
    }

    /**
     * Git "pre-commit" hook callback.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function githookPreCommit()
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
    public function test()
    {
        return $this
            ->collectionBuilder()
            ->addTaskList([
                'codecept' => $this->getTaskCodecept(),
            ]);
    }

    /**
     * Run code style checkers.
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function lint()
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
     * @return \Robo\Collection\CollectionBuilder
     */
    protected function getTaskPhpcsLint()
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

    /**
     * @return \Robo\Collection\CollectionBuilder
     */
    protected function getTaskCodecept()
    {
        $environment = $this->getEnvironment();
        $withCoverage = $environment !== 'git-hook';
        $withUnitReport = $environment !== 'git-hook';
        $logDir = $this->getLogDir();

        $cmdArgs = [];
        if ($this->isPhpDbgAvailable() && !$this->isPhpExtensionAvailable('xdebug')) {
            $cmdPattern = '%s -qrr %s';
            $cmdArgs[] = escapeshellcmd($this->phpdbgExecutable);
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

    /**
     * @param string $extension
     *
     * @return bool
     */
    protected function isPhpExtensionAvailable($extension)
    {
        $command = sprintf('%s -m', escapeshellcmd($this->phpExecutable));

        $process = new Process($command);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \RuntimeException('@todo');
        }

        return in_array($extension, explode("\n", $process->getOutput()));
    }

    /**
     * @return bool
     */
    protected function isPhpDbgAvailable()
    {
        $command = sprintf(
            '%s -i | grep -- %s',
            escapeshellcmd($this->phpExecutable),
            escapeshellarg('--enable-phpdbg')
        );

        return (new Process($command))->run() === 0;
    }

    /**
     * @return string
     */
    protected function getLogDir()
    {
        $this->initCodeceptionInfo();

        return !empty($this->codeceptionInfo['paths']['log']) ?
            $this->codeceptionInfo['paths']['log']
            : 'tests/_output';
    }
}
