<?php

namespace Cheppers\Robo\Git;

class Utils
{
    /**
     * @var string
     */
    public static $defaultTagListFormat = 'basic';

    /**
     * @var array
     */
    public static $tagListFormats = [
        'basic' => [
            'refName' => 'refname:strip=2',
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

    public static function getBoundary(): string
    {
        return md5(uniqid(rand()));
    }

    public static function createMachineReadableTagListFormatDefinition(array $properties): array
    {
        $properties += ['refName' => 'refname:strip=2'];

        $definition = [
            'key' => 'refName',
            'tagSeparator' => Utils::getBoundary(),
            'propertySeparator' => Utils::getBoundary(),
            'keyValueSeparator' => ' ',
            'format' => [],
        ];

        foreach ($properties as $key => $pattern) {
            $definition['format'][$key] = "{$key}{$definition['keyValueSeparator']}%($pattern)";
        }

        $definition['format'] = implode($definition['propertySeparator'], $definition['format']);
        $definition['format'] .= $definition['tagSeparator'];

        return $definition;
    }

    public static function parseTagListStdOutput(string $stdOutput, array $definition): array
    {
        $asset = [];
        $tags = explode($definition['tagSeparator'] . "\n", $stdOutput);
        array_pop($tags);
        foreach ($tags as $tagProperties) {
            $tag = [];
            $tagProperties = explode($definition['propertySeparator'], $tagProperties);
            foreach ($tagProperties as $property) {
                list($key, $value) = explode($definition['keyValueSeparator'], $property, 2);
                $tag[$key] = $value;
            }

            $key = $tag[$definition['key']];
            $asset[$key] = $tag;
        }

        return $asset;
    }
}
