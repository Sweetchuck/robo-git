<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\Option\OptionAllTrait;
use Sweetchuck\Robo\Git\Option\OptionColorTrait;
use Sweetchuck\Robo\Git\Option\OptionContainsTrait;
use Sweetchuck\Robo\Git\Option\OptionFormatTrait;
use Sweetchuck\Robo\Git\Option\OptionListPatternsTrait;
use Sweetchuck\Robo\Git\Option\OptionMergedTrait;
use Sweetchuck\Robo\Git\Option\OptionPointsAtTrait;
use Sweetchuck\Robo\Git\Option\OptionSortTrait;
use Robo\Contract\CommandInterface;

class GitBranchListTask extends BaseTask implements CommandInterface
{
    use OptionAllTrait;
    use OptionColorTrait;
    use OptionContainsTrait;
    use OptionFormatTrait;
    use OptionListPatternsTrait;
    use OptionMergedTrait;
    use OptionPointsAtTrait;
    use OptionSortTrait;

    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git branch list';

    /**
     * {@inheritdoc}
     */
    protected $action = 'branch';

    /**
     * @var array
     */
    protected $assets = [
        'gitBranches' => [],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormat(): string
    {
        return 'branch-list.default';
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return $this->getOptionsAll()
            + $this->getOptionsColor()
            + $this->getOptionsContains()
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

        if (array_key_exists('all', $options)) {
            $this->setAll($options['all']);
        }

        if (array_key_exists('color', $options)) {
            $this->setColor($options['color']);
        }

        if (array_key_exists('containsState', $options)) {
            $this->setContainsState($options['containsState']);
        }

        if (array_key_exists('containsValue', $options)) {
            $this->setContainsValue($options['containsValue']);
        }

        if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }

        if (array_key_exists('listPatterns', $options)) {
            $this->setListPatterns($options['listPatterns']);
        }

        if (array_key_exists('mergedState', $options)) {
            $this->setMergedState($options['mergedState']);
        }

        if (array_key_exists('mergedValue', $options)) {
            $this->setMergedValue($options['mergedValue']);
        }

        if (array_key_exists('pointsAt', $options)) {
            $this->setPointsAt($options['pointsAt']);
        }

        if (array_key_exists('sort', $options)) {
            $this->setSort($options['sort']);
        }

        return  $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        if ($this->formatMachineReadableDefinition) {
            $this->assets['gitBranches'] = $this
                ->getFormatHandler()
                ->parseStdOutput($this->actionStdOutput, $this->formatMachineReadableDefinition);
        }

        return $this;
    }
}
