<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\OutputParser\StatusParser;

class GitStatusTask extends BaseTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git status';

    /**
     * {@inheritdoc}
     */
    protected $action = 'status';

    /**
     * {@inheritdoc}
     */
    protected $assets = [
        'git.status' => [],
    ];

    // region renames
    /**
     * @var null|bool
     */
    protected $renames = null;

    public function getRenames(): ?bool
    {
        return $this->renames;
    }

    /**
     * @return $this
     */
    public function setRenames(?bool $renames)
    {
        $this->renames = $renames;

        return $this;
    }
    // endregion

    // region findRenames
    /**
     * @var null|int
     */
    protected $findRenames = null;

    public function getFindRenames(): ?int
    {
        return $this->findRenames;
    }

    /**
     * @return $this
     */
    public function setFindRenames(?int $findRenames)
    {
        $this->findRenames = $findRenames;

        return $this;
    }
    // endregion

    // region ignored
    /**
     * @var null|string
     */
    protected $ignored = null;

    public function getIgnored(): ?string
    {
        return $this->ignored;
    }

    /**
     * @return $this
     */
    public function setIgnored(?string $ignored)
    {
        $this->ignored = $ignored;

        return $this;
    }
    // endregion

    // region untrackedFiles
    /**
     * @var null|string
     */
    protected $untrackedFiles = null;

    public function getUntrackedFiles(): ?string
    {
        return $this->untrackedFiles;
    }

    /**
     * @return $this
     */
    public function setUntrackedFiles(?string $untrackedFiles)
    {
        $this->untrackedFiles = $untrackedFiles;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            '--porcelain' => [
                'type' => 'flag',
                'value' => true,
            ],
            '-z' => [
                'type' => 'flag',
                'value' => true,
            ],
            'renames' => [
                'type' => 'flag:true-value',
                'value' => $this->getRenames(),
            ],
            '--find-renames' => [
                'type' => 'flag',
                'value' => (string) $this->getFindRenames(),
            ],
            '--ignored' => [
                'type' => 'value:optional',
                'value' => $this->getIgnored(),
            ],
            '--untracked-files' => [
                'type' => 'value:optional',
                'value' => $this->getUntrackedFiles(),
            ],
        ] + parent::getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (array_key_exists('renames', $options)) {
            $this->setRenames($options['renames']);
        }

        if (array_key_exists('findRenames', $options)) {
            $this->setFindRenames($options['findRenames']);
        }

        if (array_key_exists('ignored', $options)) {
            $this->setIgnored($options['ignored']);
        }

        if (array_key_exists('untrackedFiles', $options)) {
            $this->setUntrackedFiles($options['untrackedFiles']);
        }

        return  $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        $this->assets['git.status'] = (new StatusParser())->parse(
            $this->actionExitCode,
            $this->actionStdOutput,
            $this->actionStdError
        );

        return $this;
    }
}
