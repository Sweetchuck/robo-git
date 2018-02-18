<?php

namespace Sweetchuck\Robo\Git;

class Utils
{
    public static $predefinedRefFormats = [
        'branch-list.default' => [
            'refName' => 'refname:strip=0',
            'upstream' => 'upstream:strip=0',
            'track' => 'upstream:track',
            'push' => 'push:strip=0',
            'isCurrentBranch' => 'HEAD',
        ],
        'tag-list.default' => [
            'refName' => 'refname:strip=0',
            'objectType' => 'objecttype',
            'objectName' => 'objectname',
            'taggerDate' => 'taggerdate:iso',
        ],
    ];

    /**
     * Escapes a shell argument which contains a wildcard (* or ?).
     */
    public static function escapeShellArgWithWildcard(string $arg): string
    {
        $parts = preg_split('@([\*\?]+)@', $arg, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $escaped = '';
        foreach ($parts as $part) {
            $isWildcard = (strpos($part, '*') !== false || strpos($part, '?') !== false);
            $escaped .= $isWildcard ? $part : escapeshellarg($part);
        }

        return $escaped ?: "''";
    }

    public static function filterEnabled(array $items): array
    {
        return gettype(reset($items)) === 'boolean' ? array_keys($items, true, true) : $items;
    }

    public static function getUniqueHash(): string
    {
        return md5(uniqid(rand()));
    }

    /**
     * @return string[]
     */
    public static function splitLines(string $text): array
    {
        $text = trim($text, "\r\n");

        return $text ? preg_split('/[\r\n]+/', $text) : [];
    }
}
