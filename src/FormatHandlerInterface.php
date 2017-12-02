<?php

namespace Sweetchuck\Robo\Git;

interface FormatHandlerInterface
{
    public function createMachineReadableFormatDefinition(array $properties): array;

    public function parseStdOutput(string $stdOutput, array $definition): array;
}
