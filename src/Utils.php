<?php

declare(strict_types = 1);

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
     * @deprecated Use \Sweetchuck\Utils\Filter\ArrayFilterEnabled instead.
     */
    public static function filterEnabled(array $items): array
    {
        return gettype(reset($items)) === 'boolean' ? array_keys($items, true, true) : $items;
    }

    public static function getUniqueHash(): string
    {
        return md5(uniqid('', true));
    }

    public static function parseDiffFilter(array $diffFilter): string
    {
        $statuses = [];
        foreach ($diffFilter as $statusName => $status) {
            if ($status === null) {
                continue;
            }

            $statusName = mb_strtoupper($statusName);
            $statuses[$statusName] = $status ? $statusName : mb_strtolower($statusName);
        }

        return implode('', $statuses);
    }
}
