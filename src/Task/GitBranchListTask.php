<?php

declare(strict_types=1);

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
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'all':
                    $this->setAll($value);
                    break;

                case 'color':
                    $this->setColor($value);
                    break;

                case 'containsState':
                    $this->setContainsState($value);
                    break;

                case 'containsValue':
                    $this->setContainsValue($value);
                    break;

                case 'format':
                    $this->setFormat($value);
                    break;

                case 'listPatterns':
                    $this->setListPatterns($value);
                    break;

                case 'mergedState':
                    $this->setMergedState($value);
                    break;

                case 'mergedValue':
                    $this->setMergedValue($value);
                    break;

                case 'pointsAt':
                    $this->setPointsAt($value);
                    break;

                case 'sort':
                    $this->setSort($value);
                    break;
            }
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
