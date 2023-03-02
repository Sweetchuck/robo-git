<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;
use Sweetchuck\Robo\Git\Argument\ArgumentPathsTrait;
use Sweetchuck\Robo\Git\OutputParser\StatusParser;

class GitStatusTask extends BaseTask implements CommandInterface
{
    use ArgumentPathsTrait;

    protected string $taskName = 'Git status';

    protected string $action = 'status';

    protected array $assets = [
        'git.status' => [],
    ];

    // region renames
    protected ?bool $renames = null;

    public function getRenames(): ?bool
    {
        return $this->renames;
    }

    public function setRenames(?bool $renames): static
    {
        $this->renames = $renames;

        return $this;
    }
    // endregion

    // region findRenames
    protected ?int $findRenames = null;

    public function getFindRenames(): ?int
    {
        return $this->findRenames;
    }

    public function setFindRenames(?int $findRenames): static
    {
        $this->findRenames = $findRenames;

        return $this;
    }
    // endregion

    // region ignored
    protected ?string $ignored = null;

    public function getIgnored(): ?string
    {
        return $this->ignored;
    }

    public function setIgnored(?string $ignored): static
    {
        $this->ignored = $ignored;

        return $this;
    }
    // endregion

    // region untrackedFiles
    protected ?string $untrackedFiles = null;

    public function getUntrackedFiles(): ?string
    {
        return $this->untrackedFiles;
    }

    public function setUntrackedFiles(?string $untrackedFiles): static
    {
        $this->untrackedFiles = $untrackedFiles;

        return $this;
    }
    // endregion

    protected function getOptions(): array
    {
        return
            [
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
                    'type' => 'value:optional',
                    'value' => $this->getFindRenames() === 0 ? '' : $this->getFindRenames(),
                ],
                '--ignored' => [
                    'type' => 'value:optional',
                    'value' => $this->getIgnored(),
                ],
                '--untracked-files' => [
                    'type' => 'value:optional',
                    'value' => $this->getUntrackedFiles(),
                ],
            ]
            + $this->getArgumentPaths()
            + parent::getOptions();
    }

    public function setOptions(array $options): static
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

        if (array_key_exists('paths', $options)) {
            $this->setPaths($options['paths']);
        }

        return  $this;
    }

    protected function runProcessOutputs(): static
    {
        $this->assets['git.status'] = (new StatusParser())->parse(
            $this->actionExitCode,
            $this->actionStdOutput,
            $this->actionStdError,
        );

        return $this;
    }
}
