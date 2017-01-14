<?php

namespace Cheppers\Robo\Git;

class Utils
{
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
}
