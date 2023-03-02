<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git;

class ListFilesItem implements \Stringable
{

    public ?string $status = null;

    public ?int $mask = null;

    public ?string $objectName = null;

    public ?string $unknown = null;

    public ?string $eolInfoI = null;

    public ?string $eolInfoW = null;

    public ?string $eolAttr = null;

    public ?string $fileName = null;

    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) {
                trigger_error(sprintf('Unknown property %s::%s', get_called_class(), $name));

                continue;
            }

            switch ($name) {
                case 'mask':
                    $this->$name = intval($value, 8);
                    break;

                default:
                    $this->$name = $value;
                    break;
            }
        }
    }

    public function __toString(): string
    {
        return (string) $this->fileName;
    }
}
