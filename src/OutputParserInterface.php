<?php

declare(strict_types=1);

namespace Sweetchuck\Robo\Git;

interface OutputParserInterface
{
    public function parse(int $exitCode, string $stdOutput, string $stdError): array;
}
