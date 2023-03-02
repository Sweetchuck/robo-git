<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Option;

use Sweetchuck\Robo\Git\FormatHandler;
use Sweetchuck\Robo\Git\FormatHandlerInterface;
use Sweetchuck\Robo\Git\Utils;

trait OptionFormatTrait
{
    protected array|string $format = '';

    protected ?FormatHandlerInterface $formatHandler = null;

    protected array $formatMachineReadableDefinition = [];

    abstract protected function getDefaultFormat(): string;

    public function getFormat(): array|string
    {
        return $this->format;
    }

    public function setFormat(array|string $value): static
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
