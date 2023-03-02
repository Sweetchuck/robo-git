<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\OutputParser;

use Sweetchuck\Robo\Git\OutputParserInterface;

class StatusParser implements OutputParserInterface
{
    public function parse(
        int $exitCode,
        string $stdOutput,
        string $stdError,
        array $options = [],
    ): array {
        if ($exitCode || !trim($stdOutput)) {
            return [];
        }

        $items = [];
        foreach (explode("\0", rtrim($stdOutput, "\0")) as $line) {
            $matches = [];
            preg_match('/^(?P<status>.{2}) (?P<fileName>.+)/', $line, $matches);
            $items[$matches['fileName']] = $matches['status'];
        }

        return $items;
    }
}
