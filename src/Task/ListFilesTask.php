<?php

namespace Cheppers\Robo\Git\Task;

use Cheppers\AssetJar\AssetJarAware;
use Cheppers\AssetJar\AssetJarAwareInterface;
use Cheppers\Robo\Git\ListFilesItem;
use Cheppers\Robo\Git\Utils;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\IO;
use Robo\Contract\CommandInterface;
use Robo\Contract\OutputAwareInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\TaskAccessor;
use Symfony\Component\Process\Process;

class ListFilesTask extends BaseTask implements
    AssetJarAwareInterface,
    ContainerAwareInterface,
    OutputAwareInterface,
    CommandInterface
{
    use AssetJarAware;
    use ContainerAwareTrait;
    use IO;
    use TaskAccessor;

    const STATUS_CACHED = 'H';

    const STATUS_SKIP_WORKTREE = 'S';

    const STATUS_UNMERGED = 'M';

    const STATUS_DELETED = 'R';

    const STATUS_MODIFIED = 'C';

    const STATUS_TO_BE_KILLED = 'K';

    const STATUS_OTHER = '?';

    /**
     * @var string
     */
    protected $processClass = Process::class;

    /**
     * @var array
     */
    protected $assets = [
        'workingDirectory' => '',
        'files' => [],
    ];

    /**
     * @var string[]
     */
    protected $flagOptions = [
        'separatedWithNullChar' => '-z',
        'fileStatusWithTags' => '-t',
        'lowercaseStatusLetters' => '-v',
        'showCached' => '--cached',
        'showDeleted' => '--deleted',
        'showModified' => '--modified',
        'showOthers' => '--others',
        'showIgnored' => '--ignored',
        'showStaged' => '--stage',
        'showKilled' => '--killed',
        'showOtherDirectoriesNamesOnly' => '--directory',
        'showLineEndings' => '--eol',
        'showEmptyDirectories' => '--empty-directory',
        'showUnmerged' => '--unmerged',
        'showResolveUndo' => '--resolve-undo',
        'fullName' => '--full-name',
    ];

    /**
     * @var string[]
     */
    protected $valueRequiredOptions = [
        'excludePattern' => '--exclude',
        'excludeFile' => '--exclude-file',
    ];

    //region Options.
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

    //region Option - separatedWithNullChar
    /**
     * @var bool
     */
    protected $separatedWithNullChar = false;

    public function getSeparatedWithNullChar(): bool
    {
        return $this->separatedWithNullChar;
    }

    /**
     * @return $this
     */
    public function setSeparatedWithNullChar(bool $separatedWithNullChar)
    {
        $this->separatedWithNullChar = $separatedWithNullChar;

        return $this;
    }
    //endregion

    //region Option - fileStatusWithTags
    /**
     * @var bool
     */
    protected $fileStatusWithTags = false;

    public function getFileStatusWithTags(): bool
    {
        return $this->fileStatusWithTags;
    }

    /**
     * @return $this
     */
    public function setFileStatusWithTags(bool $fileStatusWithTags)
    {
        $this->fileStatusWithTags = $fileStatusWithTags;

        return $this;
    }
    //endregion

    //region Option - lowercaseStatusLetters
    /**
     * @var bool
     */
    protected $lowercaseStatusLetters = false;

    public function getLowercaseStatusLetters(): bool
    {
        return $this->lowercaseStatusLetters;
    }

    /**
     * @return $this
     */
    public function setLowercaseStatusLetters(bool $lowercaseStatusLetters)
    {
        $this->lowercaseStatusLetters = $lowercaseStatusLetters;

        return $this;
    }
    //endregion

    //region Option - showCached
    /**
     * @var bool
     */
    protected $showCached = false;

    public function getShowCached(): bool
    {
        return $this->showCached;
    }

    /**
     * @return $this
     */
    public function setShowCached(bool $showCached)
    {
        $this->showCached = $showCached;

        return $this;
    }
    //endregion

    //region Option - showDeleted
    /**
     * @var bool
     */
    protected $showDeleted = false;

    public function getShowDeleted(): bool
    {
        return $this->showDeleted;
    }

    /**
     * @return $this
     */
    public function setShowDeleted(bool $showDeleted)
    {
        $this->showDeleted = $showDeleted;

        return $this;
    }
    //endregion

    //region Option - showModified
    /**
     * @var bool
     */
    protected $showModified = false;

    public function getShowModified(): bool
    {
        return $this->showModified;
    }

    /**
     * @return $this
     */
    public function setShowModified(bool $showModified)
    {
        $this->showModified = $showModified;

        return $this;
    }
    //endregion

    //region Option - showOthers
    /**
     * @var bool
     */
    protected $showOthers = false;

    public function getShowOthers(): bool
    {
        return $this->showOthers;
    }

    /**
     * @return $this
     */
    public function setShowOthers(bool $showOthers)
    {
        $this->showOthers = $showOthers;

        return $this;
    }
    //endregion

    //region Option - showIgnored
    /**
     * @var bool
     */
    protected $showIgnored = false;

    public function getShowIgnored(): bool
    {
        return $this->showIgnored;
    }

    /**
     * @return $this
     */
    public function setShowIgnored(bool $showIgnored)
    {
        $this->showIgnored = $showIgnored;

        return $this;
    }
    //endregion

    //region Option - showStaged
    /**
     * @var bool
     */
    protected $showStaged = false;

    public function getShowStaged(): bool
    {
        return $this->showStaged;
    }

    /**
     * @return $this
     */
    public function setShowStaged(bool $showStaged)
    {
        $this->showStaged = $showStaged;

        return $this;
    }
    //endregion

    //region Option - showKilled
    /**
     * @var bool
     */
    protected $showKilled = false;

    public function getShowKilled(): bool
    {
        return $this->showKilled;
    }

    /**
     * @return $this
     */
    public function setShowKilled(bool $showKilled)
    {
        $this->showKilled = $showKilled;

        return $this;
    }
    //endregion

    //region Option - showOtherDirectoriesNamesOnly
    /**
     * @var bool
     */
    protected $showOtherDirectoriesNamesOnly = false;

    public function getShowOtherDirectoriesNamesOnly(): bool
    {
        return $this->showOtherDirectoriesNamesOnly;
    }

    /**
     * @return $this
     */
    public function setShowOtherDirectoriesNamesOnly(bool $showOtherDirectoriesNamesOnly)
    {
        $this->showOtherDirectoriesNamesOnly = $showOtherDirectoriesNamesOnly;

        return $this;
    }
    //endregion

    //region Option - showLineEndings
    /**
     * @var bool
     */
    protected $showLineEndings = false;

    public function getShowLineEndings(): bool
    {
        return $this->showLineEndings;
    }

    /**
     * @return $this
     */
    public function setShowLineEndings(bool $showLineEndings)
    {
        $this->showLineEndings = $showLineEndings;

        return $this;
    }
    //endregion

    //region Option - showEmptyDirectories
    /**
     * @var bool
     */
    protected $showEmptyDirectories = false;

    public function getShowEmptyDirectories(): bool
    {
        return $this->showEmptyDirectories;
    }

    /**
     * @return $this
     */
    public function setShowEmptyDirectories(bool $showEmptyDirectories)
    {
        $this->showEmptyDirectories = $showEmptyDirectories;

        return $this;
    }
    //endregion

    //region Option - showUnmerged
    /**
     * @var bool
     */
    protected $showUnmerged = false;

    public function getShowUnmerged(): bool
    {
        return $this->showUnmerged;
    }

    /**
     * @return $this
     */
    public function setShowUnmerged(bool $showUnmerged)
    {
        $this->showUnmerged = $showUnmerged;

        return $this;
    }
    //endregion

    //region Option - showResolveUndo
    /**
     * @var bool
     */
    protected $showResolveUndo = false;

    public function getShowResolveUndo(): bool
    {
        return $this->showResolveUndo;
    }

    /**
     * @return $this
     */
    public function setShowResolveUndo(bool $showResolveUndo)
    {
        $this->showResolveUndo = $showResolveUndo;

        return $this;
    }
    //endregion

    //region Option - excludePattern
    /**
     * @var string
     */
    protected $excludePattern = '';

    public function getExcludePattern(): string
    {
        return $this->excludePattern;
    }

    /**
     * @return $this
     */
    public function setExcludePattern(string $excludePattern)
    {
        $this->excludePattern = $excludePattern;

        return $this;
    }
    //endregion

    //region Option - excludeFile
    /**
     * @var string
     */
    protected $excludeFile = '';

    /**
     * @return string
     */
    public function getExcludeFile(): string
    {
        return $this->excludeFile;
    }

    /**
     * @return $this
     */
    public function setExcludeFile(string $excludeFile)
    {
        $this->excludeFile = $excludeFile;

        return $this;
    }
    //endregion

    //region Option - fullName
    /**
     * @var bool
     */
    protected $fullName = false;

    public function getFullName(): bool
    {
        return $this->fullName;
    }

    /**
     * @return $this
     */
    public function setFullName(bool $fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }
    //endregion

    //region Option - paths
    /**
     * @var string[]
     */
    protected $paths = [];

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return $this
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

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
    public function setVisibleStdOutput(bool $isStdOutputVisible)
    {
        $this->visibleStdOutput = $isStdOutputVisible;

        return $this;
    }
    //endregion
    //endregion

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'assetJar':
                    $this->setAssetJar($value);
                    break;

                case 'assetJarMapping':
                    $this->setAssetJarMapping($value);
                    break;

                case 'workingDirectory':
                    $this->setWorkingDirectory($value);
                    break;

                case 'gitExecutable':
                    $this->setGitExecutable($value);
                    break;

                case 'separatedWithNullChar':
                    $this->setSeparatedWithNullChar($value);
                    break;

                case 'fileStatusWithTags':
                    $this->setFileStatusWithTags($value);
                    break;

                case 'lowercaseStatusLetters':
                    $this->setLowercaseStatusLetters($value);
                    break;

                case 'showCached':
                    $this->setShowCached($value);
                    break;

                case 'showDeleted':
                    $this->setShowDeleted($value);
                    break;

                case 'showModified':
                    $this->setShowModified($value);
                    break;

                case 'showOthers':
                    $this->setShowOthers($value);
                    break;

                case 'showIgnored':
                    $this->setShowIgnored($value);
                    break;

                case 'showStaged':
                    $this->setShowStaged($value);
                    break;

                case 'showKilled':
                    $this->setShowKilled($value);
                    break;

                case 'showOtherDirectoriesNamesOnly':
                    $this->setShowOtherDirectoriesNamesOnly($value);
                    break;

                case 'showLineEndings':
                    $this->setShowLineEndings($value);
                    break;

                case 'showEmptyDirectories':
                    $this->setShowEmptyDirectories($value);
                    break;

                case 'showUnmerged':
                    $this->setShowUnmerged($value);
                    break;

                case 'showResolveUndo':
                    $this->setShowResolveUndo($value);
                    break;

                case 'excludePattern':
                    $this->setExcludePattern($value);
                    break;

                case 'excludeFile':
                    $this->setExcludeFile($value);
                    break;

                case 'fullName':
                    $this->setFullName($value);
                    break;

                case 'paths':
                    $this->setPaths($value);
                    break;

                case 'visibleStdOutput':
                    $this->setVisibleStdOutput($value);
                    break;
            }
        }

        return  $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        /** @var \Symfony\Component\Process\Process $process */
        $process = new $this->processClass($this->getCommand());
        $exitCode = $process->run();

        if ($exitCode !== 0) {
            return Result::error($this, '@todo Error message', $this->assets);
        }

        $stdOutput = $process->getOutput();
        if ($this->isStdOutputVisible()) {
            $this->output()->write($stdOutput);
        }

        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        $this->assets['files'] = $this->parseStdOutput($stdOutput);

        $assetJar = $this->getAssetJar();
        if ($assetJar) {
            foreach ($this->assets as $name => $value) {
                if ($this->getAssetJarMap($name)) {
                    $this->setAssetJarValue($name, $value);
                }
            }
        }

        return Result::success($this, '@todo Success message', $this->assets);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand()
    {
        $options = $this->buildCommandOptions();

        $cmdPattern = '';
        $cmdArgs = [];

        $workingDir = $this->getWorkingDirectory();
        if ($workingDir) {
            $cmdPattern .= 'cd %s && ';
            $cmdArgs[] = escapeshellarg($workingDir);
        }

        $cmdPattern .= '%s ls-files';
        $cmdArgs[] = escapeshellcmd($this->getGitExecutable());

        foreach ($this->flagOptions as $optionName => $optionCli) {
            if ($options[$optionName]) {
                $cmdPattern .= " $optionCli";
            }
        }

        foreach ($this->valueRequiredOptions as $optionName => $optionCli) {
            if ($options[$optionName]) {
                $cmdPattern .= " $optionCli %s";
                $cmdArgs[] = escapeshellarg($options[$optionName]);
            }
        }

        $paths = Utils::filterEnabled($this->getPaths());
        if ($paths) {
            $cmdPattern .= ' --' . str_repeat(' %s', count($paths));
            foreach ($paths as $path) {
                $cmdArgs[] = escapeshellarg($path);
            }
        }

        return vsprintf($cmdPattern, $cmdArgs);
    }

    protected function buildCommandOptions(): array
    {
        return [
            'separatedWithNullChar' => $this->getSeparatedWithNullChar(),
            'fileStatusWithTags' => $this->getFileStatusWithTags(),
            'lowercaseStatusLetters' => $this->getLowercaseStatusLetters(),
            'showCached' => $this->getShowCached(),
            'showDeleted' => $this->getShowDeleted(),
            'showModified' => $this->getShowModified(),
            'showOthers' => $this->getShowOthers(),
            'showIgnored' => $this->getShowIgnored(),
            'showStaged' => $this->getShowStaged(),
            'showKilled' => $this->getShowKilled(),
            'showOtherDirectoriesNamesOnly' => $this->getShowOtherDirectoriesNamesOnly(),
            'showLineEndings' => $this->getShowLineEndings(),
            'showEmptyDirectories' => $this->getShowEmptyDirectories(),
            'showUnmerged' => $this->getShowUnmerged(),
            'showResolveUndo' => $this->getShowResolveUndo(),
            'excludePattern' => $this->getExcludePattern(),
            'excludeFile' => $this->getExcludeFile(),
            'fullName' => $this->getFullName(),
        ];
    }

    /**
     * @return ListFilesItem[]
     */
    protected function parseStdOutput(string $stdOutput): array
    {
        $lineSeparator = $this->getSeparatedWithNullChar() ? '\0' : '\n';
        $lines = preg_split("/{$lineSeparator}+/u", trim($stdOutput, "\n\0"), -1, PREG_SPLIT_NO_EMPTY);

        $items = [];
        $pattern = $this->getStdOutputLineParserPattern();
        foreach ($lines as $line) {
            if ($line) {
                $item = $this->parseStdOutputLine($line, $pattern);
                $items[$item->fileName] = $item;
            }
        }

        return $items;
    }

    protected function parseStdOutputLine(string $line, string $pattern = ''): ListFilesItem
    {
        $matches = null;
        preg_match($pattern, $line, $matches);

        return new ListFilesItem(array_diff_key($matches, range(0, 10)));
    }

    protected function getStdOutputLineParserPattern(): string
    {
        $fragments = [];

        if ($this->getFileStatusWithTags()) {
            $fragments[] = '(?P<status>[^\s]+)';
        }

        if ($this->getShowStaged()) {
            $fragments[] = '(?P<mask>[^\s]+)';
            $fragments[] = '(?P<objectName>[^\s]+)';
            $fragments[] = '(?P<unknown>[^\s]+)';
        }

        if ($this->getShowLineEndings()) {
            $fragments[] = '(?P<eolInfoI>[^\s]+)';
            $fragments[] = '(?P<eolInfoW>[^\s]+)';
            $fragments[] = '(?P<eolAttr>[^\s]+)';
        }

        $fragments[] = '(?P<fileName>.+)';

        return '/^' . implode('[ \t]+', $fragments) . '$/mu';
    }
}
