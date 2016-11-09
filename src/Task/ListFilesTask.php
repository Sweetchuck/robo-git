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
    protected $valueOptions = [
        'excludePattern' => '--exclude',
        'excludeFile' => '--exclude-file',
    ];

    //region Option - workingDirectory
    /**
     * @var string|null
     */
    protected $workingDirectory = null;

    /**
     * @return string|null
     */
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory($workingDirectory)
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

    /**
     * @return string
     */
    public function getGitExecutable()
    {
        return $this->gitExecutable;
    }

    /**
     * @param string $gitExecutable
     *
     * @return $this
     */
    public function setGitExecutable($gitExecutable)
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

    /**
     * @return bool
     */
    public function getSeparatedWithNullChar()
    {
        return $this->separatedWithNullChar;
    }

    /**
     * @param bool $separatedWithNullChar
     *
     * @return $this
     */
    public function setSeparatedWithNullChar($separatedWithNullChar)
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

    /**
     * @return bool
     */
    public function getFileStatusWithTags()
    {
        return $this->fileStatusWithTags;
    }

    /**
     * @param bool $fileStatusWithTags
     *
     * @return $this
     */
    public function setFileStatusWithTags($fileStatusWithTags)
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

    /**
     * @return bool
     */
    public function getLowercaseStatusLetters()
    {
        return $this->lowercaseStatusLetters;
    }

    /**
     * @param bool $lowercaseStatusLetters
     *
     * @return $this
     */
    public function setLowercaseStatusLetters($lowercaseStatusLetters)
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

    /**
     * @return bool
     */
    public function getShowCached()
    {
        return $this->showCached;
    }

    /**
     * @param bool $showCached
     *
     * @return $this
     */
    public function setShowCached($showCached)
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

    /**
     * @return bool
     */
    public function getShowDeleted()
    {
        return $this->showDeleted;
    }

    /**
     * @param bool $showDeleted
     *
     * @return $this
     */
    public function setShowDeleted($showDeleted)
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

    /**
     * @return bool
     */
    public function getShowModified()
    {
        return $this->showModified;
    }

    /**
     * @param bool $showModified
     *
     * @return $this
     */
    public function setShowModified($showModified)
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

    /**
     * @return bool
     */
    public function getShowOthers()
    {
        return $this->showOthers;
    }

    /**
     * @param bool $showOthers
     *
     * @return $this
     */
    public function setShowOthers($showOthers)
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

    /**
     * @return bool
     */
    public function getShowIgnored()
    {
        return $this->showIgnored;
    }

    /**
     * @param bool $showIgnored
     *
     * @return $this
     */
    public function setShowIgnored($showIgnored)
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

    /**
     * @return bool
     */
    public function getShowStaged()
    {
        return $this->showStaged;
    }

    /**
     * @param bool $showStaged
     *
     * @return $this
     */
    public function setShowStaged($showStaged)
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

    /**
     * @return bool
     */
    public function getShowKilled()
    {
        return $this->showKilled;
    }

    /**
     * @param bool $showKilled
     *
     * @return $this
     */
    public function setShowKilled($showKilled)
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

    /**
     * @return bool
     */
    public function getShowOtherDirectoriesNamesOnly()
    {
        return $this->showOtherDirectoriesNamesOnly;
    }

    /**
     * @param bool $showOtherDirectoriesNamesOnly
     *
     * @return ListFilesTask
     */
    public function setShowOtherDirectoriesNamesOnly($showOtherDirectoriesNamesOnly)
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

    /**
     * @return bool
     */
    public function getShowLineEndings()
    {
        return $this->showLineEndings;
    }

    /**
     * @param bool $showLineEndings
     *
     * @return $this
     */
    public function setShowLineEndings($showLineEndings)
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

    /**
     * @return bool
     */
    public function getShowEmptyDirectories()
    {
        return $this->showEmptyDirectories;
    }

    /**
     * @param bool $showEmptyDirectories
     *
     * @return ListFilesTask
     */
    public function setShowEmptyDirectories($showEmptyDirectories)
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

    /**
     * @return bool
     */
    public function getShowUnmerged()
    {
        return $this->showUnmerged;
    }

    /**
     * @param bool $showUnmerged
     *
     * @return ListFilesTask
     */
    public function setShowUnmerged($showUnmerged)
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

    /**
     * @return bool
     */
    public function getShowResolveUndo()
    {
        return $this->showResolveUndo;
    }

    /**
     * @param bool $showResolveUndo
     *
     * @return ListFilesTask
     */
    public function setShowResolveUndo($showResolveUndo)
    {
        $this->showResolveUndo = $showResolveUndo;

        return $this;
    }
    //endregion

    //region Option - excludePattern
    /**
     * @var null|string
     */
    protected $excludePattern = null;

    /**
     * @return null|string
     */
    public function getExcludePattern()
    {
        return $this->excludePattern;
    }

    /**
     * @param null|string $excludePattern
     *
     * @return $this
     */
    public function setExcludePattern($excludePattern)
    {
        $this->excludePattern = $excludePattern;

        return $this;
    }
    //endregion

    //region Option - excludeFile
    /**
     * @var null|string
     */
    protected $excludeFile = null;

    /**
     * @return null|string
     */
    public function getExcludeFile()
    {
        return $this->excludeFile;
    }

    /**
     * @param null|string $excludeFile
     *
     * @return $this
     */
    public function setExcludeFile($excludeFile)
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

    /**
     * @return bool
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param bool $fullName
     *
     * @return $this
     */
    public function setFullName($fullName)
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
     * @param array $paths
     *
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

    /**
     * @return bool
     */
    public function isStdOutputVisible()
    {
        return $this->visibleStdOutput;
    }

    /**
     * @param bool $isStdOutputVisible
     *
     * @return $this
     */
    public function setVisibleStdOutput($isStdOutputVisible)
    {
        $this->visibleStdOutput = $isStdOutputVisible;

        return $this;
    }
    //endregion

    public function __construct(array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param array $options
     *
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

        foreach ($this->valueOptions as $optionName => $optionCli) {
            if ($options[$optionName] !== null) {
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

    /**
     * @return array
     */
    protected function buildCommandOptions()
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
     * @param string $stdOutput
     *
     * @return \Cheppers\Robo\Git\ListFilesItem[]
     */
    protected function parseStdOutput($stdOutput)
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

    /**
     * @param $line
     * @param string $pattern
     *
     * @return ListFilesItem
     */
    protected function parseStdOutputLine($line, $pattern = '')
    {
        $matches = null;
        preg_match($pattern, $line, $matches);

        return new ListFilesItem(array_diff_key($matches, range(0, 10)));
    }

    /**
     * @return string
     */
    protected function getStdOutputLineParserPattern()
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
