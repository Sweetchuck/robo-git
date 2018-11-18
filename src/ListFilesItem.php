<?php

namespace Sweetchuck\Robo\Git;

class ListFilesItem
{

    /**
     * @var null|string
     */
    public $status = null;

    /**
     * @var null|int
     */
    public $mask = null;

    /**
     * @var null|string
     */
    public $objectName = null;

    /**
     * @var null|string
     */
    public $unknown = null;

    /**
     * @var null|string
     */
    public $eolInfoI = null;

    /**
     * @var null|string
     */
    public $eolInfoW = null;

    /**
     * @var null|string
     */
    public $eolAttr = null;

    /**
     * @var null|string
     */
    public $fileName = null;

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

    public function __toString()
    {
        return (string) $this->fileName;
    }
}
