<?php

namespace Sweetchuck\Robo\Git\Task;

use Robo\Contract\CommandInterface;

class GitSymbolicRefTask extends BaseTask implements CommandInterface
{
    /**
     * {@inheritdoc}
     */
    protected $taskName = 'Git symbolic-ref';

    /**
     * {@inheritdoc}
     */
    protected $action = 'symbolic-ref';

    /**
     * @var array
     */
    protected $assets = [];

    //region Options.
    // @todo
    // endregion

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            // @todo
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
                case '@todo':
                    // @todo
                    break;
            }
        }

        return  $this;
    }
}
