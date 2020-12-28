<?php

namespace Sweetchuck\Robo\Git;

interface OutputParserInterface
{
    public function parse(
        int $exitCode,
        string $stdOutput,
        string $stdError,
        array $options = []
    ): array;
}
