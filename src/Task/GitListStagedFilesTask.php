<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\OutputParser\DiffNameStatusParser;
use Sweetchuck\Robo\Git\Utils;

class GitListStagedFilesTask extends BaseTask implements BuilderAwareInterface, CommandInterface
{
    use ArgumentPathsTrait;
    use GitTaskLoader;

    protected string $taskName = 'Git - List staged files';

    protected string $action = 'diff';

    // region filePathStyle
    protected string $filePathStyle = 'relativeToTopLevel';

    /**
     * @var string[]
     */
    protected array $filePathStyleAllowedValues = [
        'relativeToTopLevel',
        'relativeToWorkingDirectory',
        'absolute',
    ];

    public function getFilePathStyle(): string
    {
        return $this->filePathStyle;
    }

    /**
     * @param string $value
     *   Allowed values:
     *   - relativeToTopLevel
     *   - relativeToWorkingDirectory
     *   - absolute
     */
    public function setFilePathStyle(string $value): static
    {
        $this->filePathStyle = $value;

        return $this;
    }
    // endregion

    // region diffFilter
    protected array $diffFilter = [];

    public function getDiffFilter(): array
    {
        return $this->diffFilter;
    }

    public function setDiffFilter(array $diffFilter): static
    {
        $this->diffFilter = $diffFilter;

        return $this;
    }
    // endregion

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('paths', $options)) {
            $this->setPaths($options['paths']);
        }

        if (array_key_exists('filePathStyle', $options)) {
            $this->setFilePathStyle($options['filePathStyle']);
        }

        if (array_key_exists('diffFilter', $options)) {
            $this->setDiffFilter($options['diffFilter']);
        }

        return $this;
    }

    protected function getOptions(): array
    {
        $filePathStyle = $this->getFilePathStyle();

        $options = [
            '--no-pager' => [
                'type' => 'flag:main',
                'value' => true,
            ],
            '--no-color' => [
                'type' => 'flag',
                'value' => true,
            ],
            '--name-status' => [
                'type' => 'flag',
                'value' => true,
            ],
            '--cached' => [
                'type' => 'flag',
                'value' => true,
            ],
            '-z' => [
                'type' => 'flag',
                'value' => true,
            ],
            '--relative' => [
                'type' => 'flag',
                'value' => $filePathStyle === 'relativeToWorkingDirectory',
            ],
            '--diff-filter' => [
                'type' => 'value:required',
                'value' => Utils::parseDiffFilter($this->getDiffFilter()),
            ],
            'argument:paths' => [
                'type' => 'arg-extra:list',
                'value' => $this->getPaths(),
            ],
        ];

        return $options + parent::getOptions();
    }

    protected function runProcessOutputs(): static
    {
        $filePathStyle = $this->getFilePathStyle();
        $outputParser = new DiffNameStatusParser();
        $outputParser->setFilePathStyle($this->getFilePathStyle());

        if ($filePathStyle === 'absolute') {
            $result = $this
                ->taskGitTopLevel()
                ->setWorkingDirectory($this->getWorkingDirectory())
                ->run()
                ->stopOnFail();

            $outputParser->setGitTopLevelDir($result['git.topLevel']);
        }

        $this->assets = $outputParser->parse(
            $this->actionExitCode,
            $this->actionStdOutput,
            $this->actionStdError
        );

        return $this;
    }
}
