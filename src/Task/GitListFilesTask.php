<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\ListFilesItem;
use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\OutputParser\ListFilesParser;

class GitListFilesTask extends BaseTask implements CommandInterface
{
    const STATUS_CACHED = 'H';

    const STATUS_SKIP_WORKTREE = 'S';

    const STATUS_UNMERGED = 'M';

    const STATUS_DELETED = 'R';

    const STATUS_MODIFIED = 'C';

    const STATUS_TO_BE_KILLED = 'K';

    const STATUS_OTHER = '?';

    protected string $taskName = 'Git list files';

    protected string $action = 'ls-files';

    protected array $assets = [
        'workingDirectory' => '',
        'files' => [],
    ];

    //region Options.

    //region Option - separatedWithNullChar
    protected bool $separatedWithNullChar = false;

    public function getSeparatedWithNullChar(): bool
    {
        return $this->separatedWithNullChar;
    }

    public function setSeparatedWithNullChar(bool $separatedWithNullChar): static
    {
        $this->separatedWithNullChar = $separatedWithNullChar;

        return $this;
    }
    //endregion

    //region Option - fileStatusWithTags
    protected bool $fileStatusWithTags = false;

    public function getFileStatusWithTags(): bool
    {
        return $this->fileStatusWithTags;
    }

    public function setFileStatusWithTags(bool $fileStatusWithTags): static
    {
        $this->fileStatusWithTags = $fileStatusWithTags;

        return $this;
    }
    //endregion

    //region Option - lowercaseStatusLetters
    protected bool $lowercaseStatusLetters = false;

    public function getLowercaseStatusLetters(): bool
    {
        return $this->lowercaseStatusLetters;
    }

    public function setLowercaseStatusLetters(bool $lowercaseStatusLetters): static
    {
        $this->lowercaseStatusLetters = $lowercaseStatusLetters;

        return $this;
    }
    //endregion

    //region Option - showCached
    protected bool $showCached = false;

    public function getShowCached(): bool
    {
        return $this->showCached;
    }

    public function setShowCached(bool $showCached): static
    {
        $this->showCached = $showCached;

        return $this;
    }
    //endregion

    //region Option - showDeleted
    protected bool $showDeleted = false;

    public function getShowDeleted(): bool
    {
        return $this->showDeleted;
    }

    public function setShowDeleted(bool $showDeleted): static
    {
        $this->showDeleted = $showDeleted;

        return $this;
    }
    //endregion

    //region Option - showModified
    protected bool $showModified = false;

    public function getShowModified(): bool
    {
        return $this->showModified;
    }

    public function setShowModified(bool $showModified): static
    {
        $this->showModified = $showModified;

        return $this;
    }
    //endregion

    //region Option - showOthers
    protected bool $showOthers = false;

    public function getShowOthers(): bool
    {
        return $this->showOthers;
    }

    public function setShowOthers(bool $showOthers): static
    {
        $this->showOthers = $showOthers;

        return $this;
    }
    //endregion

    //region Option - showIgnored
    protected bool $showIgnored = false;

    public function getShowIgnored(): bool
    {
        return $this->showIgnored;
    }

    public function setShowIgnored(bool $showIgnored): static
    {
        $this->showIgnored = $showIgnored;

        return $this;
    }
    //endregion

    //region Option - showStaged
    protected bool $showStaged = false;

    public function getShowStaged(): bool
    {
        return $this->showStaged;
    }

    public function setShowStaged(bool $showStaged): static
    {
        $this->showStaged = $showStaged;

        return $this;
    }
    //endregion

    //region Option - showKilled
    protected bool $showKilled = false;

    public function getShowKilled(): bool
    {
        return $this->showKilled;
    }

    public function setShowKilled(bool $showKilled): static
    {
        $this->showKilled = $showKilled;

        return $this;
    }
    //endregion

    //region Option - showOtherDirectoriesNamesOnly
    protected bool $showOtherDirectoriesNamesOnly = false;

    public function getShowOtherDirectoriesNamesOnly(): bool
    {
        return $this->showOtherDirectoriesNamesOnly;
    }

    public function setShowOtherDirectoriesNamesOnly(bool $showOtherDirectoriesNamesOnly): static
    {
        $this->showOtherDirectoriesNamesOnly = $showOtherDirectoriesNamesOnly;

        return $this;
    }
    //endregion

    //region Option - showLineEndings
    protected bool $showLineEndings = false;

    public function getShowLineEndings(): bool
    {
        return $this->showLineEndings;
    }

    public function setShowLineEndings(bool $showLineEndings): static
    {
        $this->showLineEndings = $showLineEndings;

        return $this;
    }
    //endregion

    //region Option - showEmptyDirectories
    protected bool $showEmptyDirectories = false;

    public function getShowEmptyDirectories(): bool
    {
        return $this->showEmptyDirectories;
    }

    public function setShowEmptyDirectories(bool $showEmptyDirectories): static
    {
        $this->showEmptyDirectories = $showEmptyDirectories;

        return $this;
    }
    //endregion

    //region Option - showUnmerged
    protected bool $showUnmerged = false;

    public function getShowUnmerged(): bool
    {
        return $this->showUnmerged;
    }

    public function setShowUnmerged(bool $showUnmerged): static
    {
        $this->showUnmerged = $showUnmerged;

        return $this;
    }
    //endregion

    //region Option - showResolveUndo
    protected bool $showResolveUndo = false;

    public function getShowResolveUndo(): bool
    {
        return $this->showResolveUndo;
    }

    public function setShowResolveUndo(bool $showResolveUndo): static
    {
        $this->showResolveUndo = $showResolveUndo;

        return $this;
    }
    //endregion

    //region Option - excludePattern
    protected string $excludePattern = '';

    public function getExcludePattern(): string
    {
        return $this->excludePattern;
    }

    public function setExcludePattern(string $excludePattern): static
    {
        $this->excludePattern = $excludePattern;

        return $this;
    }
    //endregion

    //region Option - excludeFile
    protected string $excludeFile = '';

    /**
     * @return string
     */
    public function getExcludeFile(): string
    {
        return $this->excludeFile;
    }

    public function setExcludeFile(string $excludeFile): static
    {
        $this->excludeFile = $excludeFile;

        return $this;
    }
    //endregion

    //region Option - fullName
    protected bool $fullName = false;

    public function getFullName(): bool
    {
        return $this->fullName;
    }

    public function setFullName(bool $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }
    //endregion

    //region Option - paths
    /**
     * @var string[]
     */
    protected array $paths = [];

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function setPaths(array $paths): static
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

    public function setOptions(array $options): static
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

    protected function runProcessOutputs(): static
    {
        $this->assets['workingDirectory'] = $this->getWorkingDirectory();
        $this->assets['files'] = $this->parseStdOutput();

        return $this;
    }

    /**
     * @return ListFilesItem[]
     */
    protected function parseStdOutput(): array
    {
        $parser = new ListFilesParser();

        return $parser->parse(
            $this->actionExitCode,
            $this->actionStdOutput,
            $this->actionStdError,
            [
                'separatedWithNullChar' => $this->getSeparatedWithNullChar(),
                'fileStatusWithTags' => $this->getFileStatusWithTags(),
                'showStaged' => $this->getShowStaged(),
                'showLineEndings' => $this->getShowLineEndings(),
                'lowercaseStatusLetters' => $this->getLowercaseStatusLetters(),
            ],
        );
    }
}
