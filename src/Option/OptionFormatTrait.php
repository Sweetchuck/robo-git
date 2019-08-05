<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

use Sweetchuck\Robo\Git\FormatHandler;
use Sweetchuck\Robo\Git\FormatHandlerInterface;
use Sweetchuck\Robo\Git\Utils;

trait OptionFormatTrait
{
    /**
     * @var array|string
     */
    protected $format = '';

    /**
     * @var \Sweetchuck\Robo\Git\FormatHandlerInterface
     */
    protected $formatHandler;

    /**
     * @var array
     */
    protected $formatMachineReadableDefinition = [];

    abstract protected function getDefaultFormat(): string;

    /**
     * @return array|string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param array|string $value
     *
     * @return $this
     */
    public function setFormat($value)
    {
        $this->format = $value;

        return $this;
    }

    public function getOptionsFormat(): array
    {
        $format = $this->getFormat() ?: $this->getDefaultFormat();
        if (is_string($format) && isset(Utils::$predefinedRefFormats[$format])) {
            $format = Utils::$predefinedRefFormats[$format];
        }

        if (is_array($format) && $format) {
            $this->formatMachineReadableDefinition = $this
                ->getFormatHandler()
                ->createMachineReadableFormatDefinition($format);
        }

        return [
            '--format' => [
                'type' => 'value:required',
                'value' => $this->formatMachineReadableDefinition['format'] ?? $format,
            ],
        ];
    }

    protected function getFormatHandler(): FormatHandlerInterface
    {
        if (!$this->formatHandler) {
            // @todo Setter method or a service.
            $this->formatHandler = new FormatHandler();
        }

        return $this->formatHandler;
    }
}
