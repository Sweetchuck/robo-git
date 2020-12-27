<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\ListFilesItem;
use Robo\Contract\CommandInterface;

class GitListFilesTask extends BaseTask implements CommandInterface
{
    const STATUS_CACHED = 'H';

    const STATUS_SKIP_WORKTREE = 'S';

    const STATUS_UNMERGED = 'M';

    const STATUS_DELETED = 'R';

    const STATUS_MODIFIED = 'C';

    const STATUS_TO_BE_KILLED = 'K';

    const STATUS_OTHER = '?';

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git list files';

    /**
     * {@inheritdoc}
     */
    protected $action = 'ls-files';

    /**
     * {@inheritdoc}
     */
    protected $assets = [
        'workingDirectory' => '',
        'files' => [],
    ];

    //region Options.

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
    public function getPaths(): array
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

    //endregion

    protected function getOptions(): array
    {
        return [
            '-z' => [
                'type' => 'flag',
                'value' => $this->getSeparatedWithNullChar(),
            ],
            '-t' => [
                'type' => 'flag',
                'value' => $this->getFileStatusWithTags(),
            ],
            '-v' => [
                'type' => 'flag',
                'value' => $this->getLowercaseStatusLetters(),
            ],
            '--cached' => [
                'type' => 'flag',
                'value' => $this->getShowCached(),
            ],
            '--deleted' => [
                'type' => 'flag',
                'value' => $this->getShowDeleted(),
            ],
            '--modified' => [
                'type' => 'flag',
                'value' => $this->getShowModified(),
            ],
            '--others' => [
                'type' => 'flag',
                'value' => $this->getShowOthers(),
            ],
            '--ignored' => [
                'type' => 'flag',
                'value' => $this->getShowIgnored(),
            ],
            '--stage' => [
                'type' => 'flag',
                'value' => $this->getShowStaged(),
            ],
            '--killed' => [
                'type' => 'flag',
                'value' => $this->getShowKilled(),
            ],
            '--directory' => [
                'type' => 'flag',
                'value' => $this->getShowOtherDirectoriesNamesOnly(),
            ],
            '--eol' => [
                'type' => 'flag',
                'value' => $this->getShowLineEndings(),
            ],
            '--empty-directory' => [
                'type' => 'flag',
                'value' => $this->getShowEmptyDirectories(),
            ],
            '--unmerged' => [
                'type' => 'flag',
                'value' => $this->getShowUnmerged(),
            ],
            '--resolve-undo' => [
                'type' => 'flag',
                'value' => $this->getShowResolveUndo(),
            ],
            '--full-name' => [
                'type' => 'flag',
                'value' => $this->getFullName(),
            ],
            '--exclude' => [
                'type' => 'value:required',
                'value' => $this->getExcludePattern(),
            ],
            '--exclude-file' => [
                'type' => 'value:required',
                'value' => $this->getExcludeFile(),
            ],
            'paths' => [
                'type' => 'arg-extra:list',
                'value' => $this->getPaths(),
            ],
        ] + parent::getOptions();
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (array_key_exists('separatedWithNullChar', $options)) {
            $this->setSeparatedWithNullChar($options['separatedWithNullChar']);
        }

        if (array_key_exists('fileStatusWithTags', $options)) {
            $this->setFileStatusWithTags($options['fileStatusWithTags']);
        }

        if (array_key_exists('lowercaseStatusLetters', $options)) {
            $this->setLowercaseStatusLetters($options['lowercaseStatusLetters']);
        }

        if (array_key_exists('showCached', $options)) {
            $this->setShowCached($options['showCached']);
        }

        if (array_key_exists('showDeleted', $options)) {
            $this->setShowDeleted($options['showDeleted']);
        }

        if (array_key_exists('showModified', $options)) {
            $this->setShowModified($options['showModified']);
        }

        if (array_key_exists('showOthers', $options)) {
            $this->setShowOthers($options['showOthers']);
        }

        if (array_key_exists('showIgnored', $options)) {
            $this->setShowIgnored($options['showIgnored']);
        }

        if (array_key_exists('showStaged', $options)) {
            $this->setShowStaged($options['showStaged']);
        }

        if (array_key_exists('showKilled', $options)) {
            $this->setShowKilled($options['showKilled']);
        }

        if (array_key_exists('showOtherDirectoriesNamesOnly', $options)) {
            $this->setShowOtherDirectoriesNamesOnly($options['showOtherDirectoriesNamesOnly']);
        }

        if (array_key_exists('showLineEndings', $options)) {
            $this->setShowLineEndings($options['showLineEndings']);
        }

        if (array_key_exists('showEmptyDirectories', $options)) {
            $this->setShowEmptyDirectories($options['showEmptyDirectories']);
        }

        if (array_key_exists('showUnmerged', $options)) {
            $this->setShowUnmerged($options['showUnmerged']);
        }

        if (array_key_exists('showResolveUndo', $options)) {
            $this->setShowResolveUndo($options['showResolveUndo']);
        }

        if (array_key_exists('excludePattern', $options)) {
            $this->setExcludePattern($options['excludePattern']);
        }

        if (array_key_exists('excludeFile', $options)) {
            $this->setExcludeFile($options['excludeFile']);
        }

        if (array_key_exists('fullName', $options)) {
            $this->setFullName($options['fullName']);
        }

        if (array_key_exists('paths', $options)) {
            $this->setPaths($options['paths']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        $this->assets['files'] = $this->parseStdOutput($this->actionStdOutput);

        return $this;
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
