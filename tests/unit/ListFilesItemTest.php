<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\ListFilesItem;

/**
 * @covers \Sweetchuck\Robo\Git\ListFilesItem<extended>
 */
class ListFilesItemTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

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

        $this->tester->assertEquals($expected, (string) $item);
    }
}
