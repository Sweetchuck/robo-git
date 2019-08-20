<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\Option\OptionContainsTrait;
use Sweetchuck\Robo\Git\Option\OptionFormatTrait;
use Sweetchuck\Robo\Git\Option\OptionListPatternsTrait;
use Sweetchuck\Robo\Git\Option\OptionMergedTrait;
use Sweetchuck\Robo\Git\Option\OptionPointsAtTrait;
use Sweetchuck\Robo\Git\Option\OptionSortTrait;
use Robo\Contract\CommandInterface;

class GitTagListTask extends BaseTask implements CommandInterface
{
    use OptionContainsTrait;
    use OptionFormatTrait;
    use OptionListPatternsTrait;
    use OptionMergedTrait;
    use OptionPointsAtTrait;
    use OptionSortTrait;

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git tag list';

    /**
     * {@inheritdoc}
     */
    protected $action = 'tag';

    /**
     * {@inheritdoc}
     */
    protected $assets = [
        'git.tags' => [],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormat(): string
    {
        return 'tag-list.default';
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return $this->getOptionsContains()
            + $this->getOptionsFormat()
            + $this->getOptionsListPatterns()
            + $this->getOptionsMerged()
            + $this->getOptionsPointsAt()
            + $this->getOptionsSort()
            + parent::getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (array_key_exists('mergedState', $options)) {
            $this->setMergedState($options['mergedState']);
        }

        if (array_key_exists('mergedValue', $options)) {
            $this->setMergedValue($options['mergedValue']);
        }

        if (array_key_exists('sort', $options)) {
            $this->setSort($options['sort']);
        }

        if (array_key_exists('listPatterns', $options)) {
            $this->setListPatterns($options['listPatterns']);
        }

        if (array_key_exists('containsState', $options)) {
            $this->setContainsState($options['containsState']);
        }

        if (array_key_exists('containsValue', $options)) {
            $this->setContainsValue($options['containsValue']);
        }

        if (array_key_exists('pointsAt', $options)) {
            $this->setPointsAt($options['pointsAt']);
        }

        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }

        return  $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        if ($this->formatMachineReadableDefinition) {
            $this->assets['git.tags'] = $this
                ->getFormatHandler()
                ->parseStdOutput($this->actionStdOutput, $this->formatMachineReadableDefinition);
        }

        return $this;
    }
}
