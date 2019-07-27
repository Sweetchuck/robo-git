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

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git - List staged files';

    /**
     * {@inheritdoc}
     */
    protected $action = 'diff';

    // region filePathStyle
    /**
     * @var string
     */
    protected $filePathStyle = 'relativeToTopLevel';

    /**
     * @var string[]
     */
    protected $filePathStyleAllowedValues = [
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
     *
     * @return $this
     */
    public function setFilePathStyle(string $value)
    {
        $this->filePathStyle = $value;

        return $this;
    }
    // endregion

    // region diffFilter
    /**
     * @var array
     */
    protected $diffFilter = [];

    public function getDiffFilter(): array
    {
        return $this->diffFilter;
    }

    /**
     * @return $this
     */
    public function setDiffFilter(array $diffFilter)
    {
        $this->diffFilter = $diffFilter;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (isset($options['paths'])) {
            $this->setPaths($options['paths']);
        }

        if (isset($options['filePathStyle'])) {
            $this->setFilePathStyle($options['filePathStyle']);
        }

        if (isset($options['diffFilter'])) {
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

    protected function runProcessOutputs()
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
