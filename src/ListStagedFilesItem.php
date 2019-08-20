<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

/**
 * @link https://git-scm.com/docs/git-diff#git-diff---diff-filterACDMRTUXB82308203
 */
class ListStagedFilesItem
{
    /**
     * @var null|string
     */
    public $fileName = null;

    /**
     * @var null|string
     */
    public $status = null;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                continue;
            }

            $this->$name = $value;
        }
    }
}
