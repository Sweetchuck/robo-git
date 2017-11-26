<?php

use Robo\Collection\CollectionBuilder;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Webmozart\PathUtil\Path;

class RoboFile extends \Robo\Tasks
{
    use \Sweetchuck\Robo\Git\GitTaskLoader;

    /**
     * @var array
     */
    protected $composerInfo = [];

    /**
     * @var array
     */
    protected $codeceptionInfo = [];

    /**
     * @var string[]
     */
    protected $codeceptionSuiteNames = [];

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
        return getenv($this->getEnvName('phpdbg_executable')) ?: Path::join(PHP_BINDIR, 'phpdbg');
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
                'codecept' => $this->getTaskCodeceptRunSuites(),
            ]);
    }

    /**
     * Run the Robo unit tests.
     */
    public function test(array $suiteNames): CollectionBuilder
    {
        $this->validateArgCodeceptionSuiteNames($suiteNames);

        return $this->getTaskCodeceptRunSuites($suiteNames);
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

    protected function errorOutput(): ?OutputInterface
    {
        $output = $this->output();

        return ($output instanceof ConsoleOutputInterface) ? $output->getErrorOutput() : $output;
    }

    /**
     * @return $this
     */
    protected function initEnvNamePrefix()
    {
        $this->envNamePrefix = strtoupper(str_replace('-', '_', $this->packageName));

        return $this;
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
                    'tests' => 'tests',
                    'log' => 'tests/_output',
                ],
            ];
        }

        return $this;
    }

    protected function getTaskCodeceptRunSuites(array $suiteNames = []): CollectionBuilder
    {
        if (!$suiteNames) {
            $suiteNames = ['all'];
        }

        $cb = $this->collectionBuilder();
        foreach ($suiteNames as $suiteName) {
            $cb->addTask($this->getTaskCodeceptRunSuite($suiteName));
        }

        return $cb;
    }

    protected function getTaskCodeceptRunSuite(string $suite): CollectionBuilder
    {
        $this->initCodeceptionInfo();
        $environment = $this->getEnvironment();

        $withCoverageHtml = in_array($environment, ['dev', 'git-hook']);
        $withCoverageSerialized = in_array($environment, ['jenkins', 'travis']);
        $withCoverageXml = in_array($environment, ['dev', 'jenkins', 'travis']);
        $withCoverageAny = $withCoverageSerialized || $withCoverageXml || $withCoverageHtml;

        $withUnitReportHtml = in_array($environment, ['dev', 'git-hook']);
        $withUnitReportXml = in_array($environment, ['travis', 'jenkins']);

        $logDir = $this->getLogDir();

        $cmdArgs = [];
        if ($this->isPhpDbgAvailable()) {
            $cmdPattern = '%s -qrr';
            $cmdArgs[] = escapeshellcmd($this->getPhpdbgExecutable());
        } else {
            $cmdPattern = '%s';
            $cmdArgs[] = escapeshellcmd($this->getPhpExecutable());
        }

        $cmdPattern .= ' %s';
        $cmdArgs[] = escapeshellcmd("{$this->binDir}/codecept");

        $cmdPattern .= ' --ansi';
        $cmdPattern .= ' --verbose';
        $cmdPattern .= ' --debug';

        $tasks = [];
        if ($withCoverageHtml) {
            $cmdPattern .= ' --coverage-html=%s';
            $cmdArgs[] = escapeshellarg("test/$suite/coverage/html");
        }

        if ($withCoverageXml) {
            $cmdPattern .= ' --coverage-xml=%s';
            $cmdArgs[] = escapeshellarg("test/$suite/coverage/coverage.xml");
        }

        if ($withCoverageAny) {
            $cmdPattern .= ' --coverage=%s';
            $cmdArgs[] = escapeshellarg("test/$suite/coverage/coverage.serialized");

            $tasks['prepareCoverageDir'] = $this
                ->taskFilesystemStack()
                ->mkdir("$logDir/test/$suite/coverage");
        }

        if ($withUnitReportHtml) {
            $cmdPattern .= ' --html=%s';
            $cmdArgs[] = escapeshellarg("test/$suite/junit/junit.html");
        }

        if ($withUnitReportXml) {
            $cmdPattern .= ' --xml=%s';
            $cmdArgs[] = escapeshellarg("test/$suite/junit/junit.xml");
        }

        if ($withUnitReportXml || $withUnitReportHtml) {
            $tasks['prepareJUnitDir'] = $this
                ->taskFilesystemStack()
                ->mkdir("$logDir/test/$suite/junit");
        }

        $cmdPattern .= ' run';
        if ($suite !== 'all') {
            $cmdPattern .= ' %s';
            $cmdArgs[] = escapeshellarg($suite);
        }

        if ($environment === 'jenkins') {
            // Jenkins has to use a post-build action to mark the build "unstable".
            $cmdPattern .= ' || [[ "${?}" == "1" ]]';
        }

        $command = vsprintf($cmdPattern, $cmdArgs);

        return $this
            ->collectionBuilder()
            ->addTaskList($tasks)
            ->addCode(function () use ($command) {
                $this->output()->writeln(strtr(
                    '<question>[{name}]</question> runs <info>{command}</info>',
                    [
                        '{name}' => 'Codeception',
                        '{command}' => $command,
                    ]
                ));
                $process = new Process($command);
                $exitCode = $process->run(function ($type, $data) {
                    switch ($type) {
                        case Process::OUT:
                            $this->output()->write($data);
                            break;

                        case Process::ERR:
                            $this->errorOutput()->write($data);
                            break;
                    }
                });

                return $exitCode;
            });
    }

    protected function getTaskPhpcsLint(): CollectionBuilder
    {
        $env = $this->getEnvironment();

        return $this->collectionBuilder()->addCode(function () use ($env) {
            $execStack = $this->taskExecStack();
            $numOfCommands = 0;

            $cmdPattern = '%s';
            $cmdArgs = [escapeshellcmd('bin/phpcs')];

            $cmdPattern .= ' --colors';

            $cmdPattern .= ' --report=%s';
            $cmdArgs[] = 'full';

            if ($env === 'git-hook') {
                $files = [
                    'src/',
                    'tests/_support/Helper/',
                    'tests/acceptance/',
                    'tests/unit/',
                    'RoboFile.php',
                ];

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
                $numOfCommands++;
                $execStack->exec(vsprintf($cmdPattern, $cmdArgs));
            }

            return $numOfCommands ? $execStack->run() : 0;
        });
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

    protected function getCodeceptionSuiteNames(): array
    {
        if (!$this->codeceptionSuiteNames) {
            $this->initCodeceptionInfo();

            /** @var \Symfony\Component\Finder\Finder $suiteFiles */
            $suiteFiles = Finder::create()
                ->in($this->codeceptionInfo['paths']['tests'])
                ->files()
                ->name('*.suite.yml')
                ->depth(0);

            foreach ($suiteFiles as $suiteFile) {
                $this->codeceptionSuiteNames[] = $suiteFile->getBasename('.suite.yml');
            }
        }

        return $this->codeceptionSuiteNames;
    }

    protected function validateArgCodeceptionSuiteNames(array $suiteNames): void
    {
        if (!$suiteNames) {
            return;
        }

        $invalidSuiteNames = array_diff($suiteNames, $this->getCodeceptionSuiteNames());
        if ($invalidSuiteNames) {
            throw new \InvalidArgumentException(
                'The following Codeception suite names are invalid: ' . implode(', ', $invalidSuiteNames),
                1
            );
        }
    }
}
