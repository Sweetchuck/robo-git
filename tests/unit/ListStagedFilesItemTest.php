<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Tests\Unit;

use Codeception\Test\Unit;
use Sweetchuck\Robo\Git\ListStagedFilesItem;

/**
 * @covers \Sweetchuck\Robo\Git\ListStagedFilesItem
 */
class ListStagedFilesItemTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\Git\Test\UnitTester
     */
    protected $tester;

    public function casesConstruct(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'values' => [
                [
                    'fileName' => 'foo',
                    'status' => 'AA',
                ],
                [
                    'fileName' => 'foo',
                    'status' => 'AA',
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesConstruct
     */
    public function testConstruct($expected, array $args): void
    {
        $expected += [
            'fileName' => null,
            'status' => null,
        ];

        $item = new ListStagedFilesItem($args);

        $this->tester->assertSame($expected['fileName'], $item->fileName);
        $this->tester->assertSame($expected['status'], $item->status);
    }
}
