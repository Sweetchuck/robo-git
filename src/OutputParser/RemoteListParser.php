<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\OutputParser;

use Sweetchuck\Robo\Git\OutputParserInterface;

class RemoteListParser implements OutputParserInterface
{
    public function parse(int $exitCode, string $stdOutput, string $stdError): array
    {
        $stdOutput = trim($stdOutput);
        if ($exitCode || !$stdOutput) {
            return [];
        }

        $items = [];

        $pattern = '/^(?P<name>[^\s]+)\s+(?P<url>.+) \((?P<type>.+)\)$/';
        foreach (preg_split('/\s*?\n\s*/', $stdOutput) as $line) {
            $matches = [];
            if (!preg_match($pattern, $line, $matches)) {
                continue;
            }

            $items[$matches['name']][$matches['type']] = $matches['url'];
        }

        return $items;
    }
}
