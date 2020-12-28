<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\OutputParser;

use Sweetchuck\Robo\Git\ListFilesItem;
use Sweetchuck\Robo\Git\OutputParserInterface;

class ListFilesParser implements OutputParserInterface
{

    /**
     * @var array
     */
    protected $options = [];

    public function parse(
        int $exitCode,
        string $stdOutput,
        string $stdError,
        array $options = []
    ): array {
        $this->options = $options;
        $this->options += [
            'separatedWithNullChar' => false,
            'fileStatusWithTags' => false,
            'showStaged' => false,
            'showLineEndings' => false,
            'lowercaseStatusLetters' => false,
        ];

        $lineSeparator = $this->options['separatedWithNullChar'] ? '\0' : '\n';
        $lines = preg_split("/{$lineSeparator}+/u", trim($stdOutput, "\n\0"), -1, PREG_SPLIT_NO_EMPTY);

        $items = [];
        $pattern = $this->getStdOutputLineParserPattern();
        foreach ($lines as $line) {
            if ($line) {
                $item = $this->parseStdOutputLine($line, $pattern);
                $items[$item->fileName] = $item;
            }
        }

        return $items;
    }

    protected function parseStdOutputLine(string $line, string $pattern): ListFilesItem
    {
        $matches = null;
        preg_match($pattern, $line, $matches);

        return new ListFilesItem(array_diff_key($matches, range(0, 10)));
    }

    protected function getStdOutputLineParserPattern(): string
    {
        $fragments = [];

        if ($this->options['fileStatusWithTags'] || $this->options['lowercaseStatusLetters']) {
            $fragments[] = '(?P<status>[^\s]+)';
        }

        if ($this->options['showStaged']) {
            $fragments[] = '(?P<mask>[^\s]+)';
            $fragments[] = '(?P<objectName>[^\s]+)';
            $fragments[] = '(?P<unknown>[^\s]+)';
        }

        if ($this->options['showLineEndings']) {
            $fragments[] = '(?P<eolInfoI>[^\s]+)';
            $fragments[] = '(?P<eolInfoW>[^\s]+)';
            $fragments[] = '(?P<eolAttr>[^\s]+)';
        }

        $fragments[] = '(?P<fileName>.+)';

        return '/^' . implode('[ \t]+', $fragments) . '$/mu';
    }
}
