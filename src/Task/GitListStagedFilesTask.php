<?php

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\BuilderAwareInterface;
use Sweetchuck\Robo\Git\GitTaskLoader;
use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\Utils;
use Webmozart\PathUtil\Path;

class GitListStagedFilesTask extends BaseTask implements BuilderAwareInterface
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

        return $this;
    }

    protected function getOptions(): array
    {
        $filePathStyle = $this->getFilePathStyle();

        $options = [
            '--name-only' => [
                'type' => 'flag',
                'value' => true,
            ],
            '--cached' => [
                'type' => 'flag',
                'value' => true,
            ],
            '--relative' => [
                'type' => 'flag',
                'value' => $filePathStyle === 'relativeToWorkingDirectory',
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
        $this->assets['files'] = [];
        if ($this->actionStdOutput === '' || $this->actionExitCode !== 0) {
            return $this;
        }

        $this->assets['files'] = Utils::splitLines($this->actionStdOutput);

        $filePathStyle = $this->getFilePathStyle();
        if ($filePathStyle === 'relativeToTopLevel') {
            return $this;
        }

        if ($filePathStyle === 'relativeToWorkingDirectory') {
            foreach ($this->assets['files'] as $key => $file) {
                $this->assets['files'][$key] = "./$file";
            }

            return $this;
        }

        $result = $this
            ->taskGitTopLevel()
            ->setWorkingDirectory($this->getWorkingDirectory())
            ->run()
            ->stopOnFail();

        $gitTopLevel = $result['git.topLevel'];
        foreach ($this->assets['files'] as $key => $file) {
            $this->assets['files'][$key] = Path::join($gitTopLevel, $file);
        }

        return $this;
    }
}
