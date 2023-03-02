<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Task;

use Sweetchuck\Robo\Git\Argument\ArgumentRevRangeTrait;

class GitListChangedFilesTask extends GitListStagedFilesTask
{
    use ArgumentRevRangeTrait;

    public function setOptions(array $options): static
    {
        parent::setOptions($options);

        if (array_key_exists('fromRevName', $options)) {
            $this->setFromRevName($options['fromRevName']);
        }

        if (array_key_exists('toRevName', $options)) {
            $this->setToRevName($options['toRevName']);
        }

        return $this;
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions() + $this->getArgumentRevRange();
        unset($options['--cached']);

        return $options;
    }
}
