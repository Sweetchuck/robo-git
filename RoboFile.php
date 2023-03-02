<?php

declare(strict_types = 1);

use Consolidation\AnnotatedCommand\CommandData;
use NuvoleWeb\Robo\Task\Config\Robo\loadTasks as ConfigLoader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Tasks;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ConfigAwareTrait;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Sweetchuck\Utils\Filter\ArrayFilterEnabled;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class RoboFile extends Tasks implements LoggerAwareInterface, \Robo\Contract\ConfigAwareInterface
{
    use ConfigLoader;
    use LoggerAwareTrait;
    use ConfigAwareTrait;
    use GitTaskLoader;

    protected array $composerInfo = [];

    protected array $codeceptionInfo = [];

    /**
     * @var string[]
     */
    protected array $codeceptionSuiteNames = [];

    protected string $packageVendor = '';

    protected string $packageName = '';

    protected string $binDir = 'vendor/bin';

    protected string $gitHook = '';

    protected string $envVarNamePrefix = '';

    /**
     * Allowed values: dev, ci, prod.
     */
    protected string $environmentType = '';

    /**
     * Allowed values: local, jenkins, travis, circleci.
     */
    protected string $environmentName = '';

    /**
     * RoboFile constructor.
     */
    public function __construct()
    {
        $this
            ->initComposerInfo()
            ->initEnvVarNamePrefix()
            ->initEnvironmentTypeAndName();
    }

    /**
     * Git "pre-commit" hook callback.
     */
    public function githookPreCommit(): CollectionBuilder
    {
        $this->gitHook = 'pre-commit';

        return $this
            ->collectionBuilder()
            ->addTask($this->taskComposerValidate())
            ->addTask($this->getTaskPhpcsLint())
            ->addTask($this->getTaskCodeceptRunSuites());
    }

    /**
     * @hook validate test
     */
    public function inputSuitNamesValidateOptionalArg(CommandData $commandData)
    {
        $args = $commandData->arguments();
        $this->validateArgCodeceptionSuiteNames($args['suiteNames']);
    }

    /**
     * Run the Robo unit tests.
     */
    public function test(
        array $suiteNames,
        array $options = [
            'debug' => false,
        ]
    ): CollectionBuilder {
        return $this->getTaskCodeceptRunSuites($suiteNames, $options);
    }

    /**
     * Run code style checkers.
     */
    public function lint(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addTask($this->taskComposerValidate())
            ->addTask($this->getTaskPhpcsLint());
    }

    protected function errorOutput(): ?OutputInterface
    {
        $output = $this->output();

        return ($output instanceof ConsoleOutputInterface) ? $output->getErrorOutput() : $output;
    }

    /**
     * @return $this
     */
    protected function initEnvVarNamePrefix()
    {
        $this->envVarNamePrefix = strtoupper(str_replace('-', '_', $this->packageName));

        return $this;
    }

    /**
     * @return $this
     */
    protected function initEnvironmentTypeAndName()
    {
        $this->environmentType = (string) getenv($this->getEnvVarName('environment_type'));
        $this->environmentName = (string) getenv($this->getEnvVarName('environment_name'));

        if (!$this->environmentType) {
            if (getenv('CI') === 'true') {
                // Travis, GitLab and CircleCI.
                $this->environmentType = 'ci';
            } elseif (getenv('JENKINS_HOME')) {
                $this->environmentType = 'ci';
                if (!$this->environmentName) {
                    $this->environmentName = 'jenkins';
                }
            }
        }

        if (!$this->environmentName && $this->environmentType === 'ci') {
            if (getenv('GITLAB_CI') === 'true') {
                $this->environmentName = 'gitlab';
            } elseif (getenv('TRAVIS') === 'true') {
                $this->environmentName = 'travis';
            } elseif (getenv('CIRCLECI') === 'true') {
                $this->environmentName = 'circleci';
            }
        }

        if (!$this->environmentType) {
            $this->environmentType = 'dev';
        }

        if (!$this->environmentName) {
            $this->environmentName = 'local';
        }

        return $this;
    }

    protected function getEnvVarName(string $name): string
    {
        return "{$this->envVarNamePrefix}_" . strtoupper($name);
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
        [$this->packageVendor, $this->packageName] = explode('/', $this->composerInfo['name']);

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
                    'log' => 'tests/_log',
                    'envs' => 'tests/_envs',
                ],
            ];
        }

        return $this;
    }

    protected function getTaskCodeceptRunSuites(array $suiteNames = [], array $options = []): CollectionBuilder
    {
        if (!$suiteNames) {
            $suiteNames = ['all'];
        }

        $phpExecutables = $this->getEnabledPhpExecutables();
        $cb = $this->collectionBuilder();
        foreach ($suiteNames as $suiteName) {
            foreach ($phpExecutables as $phpExecutable) {
                $cb->addTask($this->getTaskCodeceptRunSuite($suiteName, $phpExecutable));
            }
        }

        return $cb;
    }

    protected function getTaskCodeceptRunSuite(string $suite, array $php): CollectionBuilder
    {
        $this->initCodeceptionInfo();

        $withCoverageHtml = $this->environmentType === 'dev';
        $withCoverageXml = $this->environmentType === 'ci';

        $withUnitReportHtml = $this->environmentType === 'dev';
        $withUnitReportXml = $this->environmentType === 'ci';

        $logDir = $this->getLogDir();

        $cmdPattern = '';
        $cmdArgs = [];
        foreach ($php['envVars'] ?? [] as $envName => $envValue) {
            $cmdPattern .= $envName;
            if ($envValue === null) {
                $cmdPattern .= ' ';
            } else {
                $cmdPattern .= '=%s ';
                $cmdArgs[] = escapeshellarg($envValue);
            }
        }

        $cmdPattern .= '%s';
        $cmdArgs[] = $php['command'];

        $cmdPattern .= ' %s';
        $cmdArgs[] = escapeshellcmd("{$this->binDir}/codecept");

        $cmdPattern .= ' --ansi';
        $cmdPattern .= ' --verbose';
        $cmdPattern .= ' --debug';

        $cb = $this->collectionBuilder();
        if ($withCoverageHtml) {
            $cmdPattern .= ' --coverage-html=%s';
            $cmdArgs[] = escapeshellarg("human/coverage/$suite/html");

            $cb->addTask(
                $this
                    ->taskFilesystemStack()
                    ->mkdir("$logDir/human/coverage/$suite")
            );
        }

        if ($withCoverageXml) {
            $cmdPattern .= ' --coverage-xml=%s';
            $cmdArgs[] = escapeshellarg("machine/coverage/$suite/coverage.xml");
        }

        if ($withCoverageHtml || $withCoverageXml) {
            $cmdPattern .= ' --coverage=%s';
            $cmdArgs[] = escapeshellarg("machine/coverage/$suite/coverage.serialized");

            $cb->addTask(
                $this
                    ->taskFilesystemStack()
                    ->mkdir("$logDir/machine/coverage/$suite")
            );
        }

        if ($withUnitReportHtml) {
            $cmdPattern .= ' --html=%s';
            $cmdArgs[] = escapeshellarg("human/junit/junit.$suite.html");

            $cb->addTask(
                $this
                    ->taskFilesystemStack()
                    ->mkdir("$logDir/human/junit")
            );
        }

        if ($withUnitReportXml) {
            $cmdPattern .= ' --xml=%s';
            $cmdArgs[] = escapeshellarg("machine/junit/junit.$suite.xml");

            $cb->addTask(
                $this
                    ->taskFilesystemStack()
                    ->mkdir("$logDir/machine/junit")
            );
        }

        $cmdPattern .= ' run';
        if ($suite !== 'all') {
            $cmdPattern .= ' %s';
            $cmdArgs[] = escapeshellarg($suite);
        }

        $envDir = $this->codeceptionInfo['paths']['envs'];
        $envFileName = "{$this->environmentType}.{$this->environmentName}";
        if (file_exists("$envDir/$envFileName.yml")) {
            $cmdPattern .= ' --env %s';
            $cmdArgs[] = escapeshellarg($envFileName);
        }

        if ($this->environmentType === 'ci' && $this->environmentName === 'jenkins') {
            // Jenkins has to use a post-build action to mark the build "unstable".
            $cmdPattern .= ' || [[ "${?}" == "1" ]]';
        }

        $command = vsprintf($cmdPattern, $cmdArgs);

        return $cb
            ->addCode(function () use ($command, $php) {
                $this->output()->writeln(strtr(
                    '<question>[{name}]</question> runs <info>{command}</info>',
                    [
                        '{name}' => 'Codeception',
                        '{command}' => $command,
                    ]
                ));

                $process = Process::fromShellCommandline(
                    $command,
                    null,
                    $php['envVars'] ?? null,
                    null,
                    null,
                );

                return $process->run(function ($type, $data) {
                    switch ($type) {
                        case Process::OUT:
                            $this->output()->write($data);
                            break;

                        case Process::ERR:
                            $this->errorOutput()->write($data);
                            break;
                    }
                });
            });
    }

    protected function getTaskPhpcsLint(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addCode(function () {
                $execStack = $this->taskExecStack();

                $cmdPattern = '%s';
                $cmdArgs = [escapeshellcmd("{$this->binDir}/phpcs")];

                $cmdPattern .= ' --colors';

                $cmdPattern .= ' --report=%s';
                $cmdArgs[] = 'full';

                if ($this->gitHook === 'pre-commit') {
                     $result = $this
                        ->collectionBuilder()
                        ->addTask(
                            $this
                                ->taskGitListStagedFiles()
                                ->setPaths(['*.php' => true])
                                ->setDiffFilter(['d' => false])
                                ->setAssetNamePrefix('staged.')
                        )
                        ->addTask(
                            $this
                                ->taskGitReadStagedFiles()
                                ->setCommandOnly(true)
                                ->deferTaskConfiguration('setPaths', 'staged.fileNames')
                        )
                        ->run();

                    if (empty($result['files'])) {
                        $this->logger->info('There is no PHP file added to the Git stage');

                        return 0;
                    }

                    $cmdPattern = '%s | ' . $cmdPattern . ' --stdin-path=%s';
                    $cmdArgs = ['fileContentCmd' => ''] + $cmdArgs + ['fileName' => ''];

                    foreach ($result['files'] as $file) {
                        $cmdArgs['fileContentCmd'] = $file['command'];
                        $cmdArgs['fileName'] = escapeshellarg($file['fileName']);

                        $execStack->exec(vsprintf($cmdPattern, $cmdArgs));
                    }

                    return $execStack->run();
                }

                if ($this->environmentType === 'ci') {
                    $dir = 'tests/_log/machine/checkstyle';
                    $execStack->exec(sprintf('mkdir -p %s', escapeshellarg($dir)));
                    $cmdPattern .= ' --report-checkstyle=%s';
                    $cmdArgs[] = escapeshellarg("$dir/phpcs.psr2.xml");
                }

                $execStack->exec(vsprintf($cmdPattern, $cmdArgs));

                return $execStack->run();
            });
    }

    protected function getLogDir(): string
    {
        $this->initCodeceptionInfo();

        return !empty($this->codeceptionInfo['paths']['log']) ?
            $this->codeceptionInfo['paths']['log']
            : 'tests/_log';
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
                ->name('*.suite.dist.yml')
                ->depth(0);

            foreach ($suiteFiles as $suiteFile) {
                $parts = explode('.', $suiteFile->getBasename());
                $this->codeceptionSuiteNames[] = reset($parts);
            }
        }

        return $this->codeceptionSuiteNames;
    }

    /**
     * @return $this
     */
    protected function validateArgCodeceptionSuiteNames(array $suiteNames)
    {
        $invalidSuiteNames = array_diff($suiteNames, $this->getCodeceptionSuiteNames());
        if ($invalidSuiteNames) {
            throw new \InvalidArgumentException(
                'The following Codeception suite names are invalid: ' . implode(', ', $invalidSuiteNames),
                1
            );
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getEnabledPhpExecutables(): array
    {
        return array_filter(
            $this->getConfig()->get('php.executables'),
            new ArrayFilterEnabled(),
        );
    }
}
