<?php

namespace Sweetchuck\Robo\Git\Tests\Unit;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sweetchuck\Robo\Git\ListFilesItem;

class ListFilesItemTest extends TestCase
{

    public function casesToString(): array
    {
        return [
            'basic' => [
                'a/b/c.php',
                [
                    'fileName' => 'a/b/c.php',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesToString
     */
    public function testToString($expected, array $args): void
    {
        $item = new ListFilesItem($args);

        Assert::assertEquals($expected, (string) $item);
    }
}
