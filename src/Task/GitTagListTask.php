<?php

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\Utils;
use Robo\Contract\CommandInterface;

class GitTagListTask extends BaseTask implements CommandInterface
{
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
        'tags' => [],
    ];

    //region Options.

    // region Option - mergedState.
    /**
     * @var null|bool
     */
    protected $mergedState = null;

    public function getMergedState(): ?bool
    {
        return $this->mergedState;
    }

    /**
     * @return $this
     */
    public function setMergedState(?bool $value)
    {
        $this->mergedState = $value;

        return $this;
    }
    // endregion

    // region Option - mergedValue.
    /**
     * @var string
     */
    protected $mergedValue = '';

    public function getMergedValue(): string
    {
        return $this->mergedValue;
    }

    /**
     * @return $this
     */
    public function setMergedValue(string $value)
    {
        $this->mergedValue = $value;

        return $this;
    }
    // endregion

    // region Option - sort.
    /**
     * @var string
     */
    protected $sort = '';

    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @return $this
     */
    public function setSort(string $value)
    {
        $this->sort = $value;

        return $this;
    }
    // endregion

    // region Option - listPatterns.
    /**
     * @var array
     */
    protected $listPatterns = [];

    public function getListPatterns(): array
    {
        return $this->listPatterns;
    }

    /**
     * @return $this
     */
    public function setListPatterns(array $value)
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->listPatterns = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function addListPatterns(array $value)
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, true);
        }

        $this->listPatterns = $value + $this->listPatterns;

        return $this;
    }

    /**
     * @return $this
     */
    public function addListPattern(string $value)
    {
        $this->listPatterns[$value] = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeListPatterns(array $value)
    {
        if (gettype(reset($value)) !== 'boolean') {
            $value = array_fill_keys($value, false);
        }

        $this->listPatterns = $value + $this->listPatterns;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeListPattern(string $value)
    {
        $this->listPatterns[$value] = false;

        return $this;
    }
    // endregion

    // region Option - contains.
    /**
     * @var null|string
     */
    protected $contains = null;

    public function getContains(): ?string
    {
        return $this->contains;
    }

    /**
     * @return $this
     */
    public function setContains(?string $value)
    {
        $this->contains = $value;

        return $this;
    }
    // endregion

    // region Option - pointsAt.
    /**
     * @var string
     */
    protected $pointsAt = '';

    public function getPointsAt(): string
    {
        return $this->pointsAt;
    }

    /**
     * @return $this
     */
    public function setPointsAt(string $value)
    {
        $this->pointsAt = $value;

        return $this;
    }
    // endregion

    // region Option - format.
    /**
     * @var array|string
     */
    protected $format = '';

    /**
     * @return array|string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param array|string
     *
     * @return $this
     */
    public function setFormat($value)
    {
        $this->format = $value;

        return $this;
    }
    // endregion

    // endregion

    /**
     * @var array
     */
    protected $machineReadableDefinition = [];

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        $format = $this->getFormat();
        if (is_array($format) && $format) {
            $this->machineReadableDefinition = Utils::createMachineReadableTagListFormatDefinition($format);
        }

        return [
            'merged' => [
                'type' => 'value:state',
                'state' => $this->getMergedState(),
                'value' => $this->getMergedValue(),
            ],
            '--sort' => [
                'type' => 'value:required',
                'value' => $this->getSort(),
            ],
            '--list' => [
                'type' => 'value:multi',
                'value' => $this->getListPatterns(),
            ],
            '--contains' => [
                'type' => 'value:optional',
                'value' => $this->getContains(),
            ],
            '--points-at' => [
                'type' => 'value:required',
                'value' => $this->getPointsAt(),
            ],
            '--format' => [
                'type' => 'value:required',
                'value' => $this->machineReadableDefinition['format'] ?? $format,
            ],
        ] + parent::getOptions();
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

                case 'contains':
                    $this->setContains($value);
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
        if ($this->machineReadableDefinition) {
            $this->assets['tags'] = Utils::parseTagListStdOutput(
                $this->actionStdOutput,
                $this->machineReadableDefinition
            );
        }

        return $this;
    }
}
