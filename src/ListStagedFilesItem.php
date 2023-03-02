<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

/**
 * @link https://git-scm.com/docs/git-diff#git-diff---diff-filterACDMRTUXB82308203
 */
class ListStagedFilesItem
{
    public ?string $fileName = null;

    public ?string $status = null;

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
