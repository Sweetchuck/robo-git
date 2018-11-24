<?php

declare(strict_types=1);

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
     * @var array
     */
    protected $assets = [
        'gitTags' => [],
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
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'mergedState':
                    $this->setMergedState($value);
                    break;

                case 'mergedValue':
                    $this->setMergedValue($value);
                    break;

                case 'sort':
                    $this->setSort($value);
                    break;

                case 'listPatterns':
                    $this->setListPatterns($value);
                    break;

                case 'containsState':
                    $this->setContainsState($value);
                    break;

                case 'containsValue':
                    $this->setContainsValue($value);
                    break;

                case 'pointsAt':
                    $this->setPointsAt($value);
                    break;

                case 'format':
                    $this->setFormat($value);
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
            $this->assets['git.tags'] = $this
                ->getFormatHandler()
                ->parseStdOutput($this->actionStdOutput, $this->formatMachineReadableDefinition);
        }

        return $this;
    }
}
