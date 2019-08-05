<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitNumOfCommitsBetweenTask extends BaseTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git number of commits between';

    /**
     * {@inheritdoc}
     */
    protected $action = 'rev-list';

    /**
     * @var array
     */
    protected $assets = [
        'numOfCommits' => null,
    ];

    // region Option - fromRevName.
    /**
     * @var string
     */
    protected $fromRevName = '';

    public function getFromRevName(): string
    {
        return $this->fromRevName;
    }

    /**
     * @return $this
     */
    public function setFromRevName(string $value)
    {
        $this->fromRevName = $value;

        return $this;
    }
    // endregion

    // region Option - toRevName.
    /**
     * @var string
     */
    protected $toRevName = 'HEAD';

    public function getToRevName(): string
    {
        return $this->toRevName;
    }

    /**
     * @return $this
     */
    public function setToRevName(string $value)
    {
        $this->toRevName = $value;

        return $this;
    }
    // endregion

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            '--count' => [
                'type' => 'flag',
                'value' => true,
            ],
            'range' => [
                'type' => 'arg-normal',
                'value' => sprintf('%s..%s', $this->getFromRevName(), $this->getToRevName()),
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
                case 'fromRevName':
                    $this->setFromRevName($value);
                    break;

                case 'toRevName':
                    $this->setToRevName($value);
                    break;
            }
        }

        return  $this;
    }

    protected function runValidate()
    {
        if (!$this->getFromRevName()) {
            throw new \Exception('The "from" rev name is missing', 1);
        }

        if (!$this->getToRevName()) {
            throw new \Exception('The "to" rev name is missing', 1);
        }

        return parent::runValidate();
    }

    /**
     * {@inheritdoc}
     */
    protected function runProcessOutputs()
    {
        $this->assets['numOfCommits'] = (int) trim($this->actionStdOutput);

        return $this;
    }
}
